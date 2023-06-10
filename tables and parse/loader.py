"""
Загрузчик данных о ходе приёмной кампании из 1С Университет
"""
import logging
import psutil
from model import *
from sqlalchemy.orm import sessionmaker
from sqlalchemy import create_engine, select, insert
from service import Service1C
from zeep import xsd
from zeep.exceptions import Fault
from re import search

sources = [
    {
        'schema': 'http',
        'host': '10.0.16.142',
        'base': 'Abit2023',
        'login': 'smiabitur',
        'password': 'Qw123321wq',
    }
]

forms = ['Очная', 'Очно-заочная', 'Заочная']


def decode_level(code: str) -> str:
    return search(r'(\d+).(\d+)\.(\d+)', code).group(2)


if __name__ == '__main__':

    argSrc = 0  # Источник
    logging.basicConfig(
        format='%(asctime)s - %(message)s',
        level=logging.INFO,
    )

    logging.info(f'Запущен загрузчик сводки с источником {argSrc}.')
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')

    wsdlURL = f'{sources[argSrc]["schema"]}://{sources[argSrc]["host"]}/{sources[argSrc]["base"]}/ws/svodka.1cws?wsdl'
    srvSvd = Service1C(wsdlURL=wsdlURL,
                       serviceName='ServisSvodka',
                       portName='ServisSvodkaSoap',
                       login=sources[argSrc]['login'],
                       password=sources[argSrc]['password'])
    srvSvd.setNamespace('abit', 'http://portal.sgu.ru/abit')
    logging.info(f"Установлено соединение с сервисом 1С Университет.")
    logging.debug(f'{wsdlURL}; serviceName="ServisSvodka"; portName="ServisSvodkaSoap"')
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')
    try:
        data = srvSvd.client.service.getSvodka()
    except Fault as e:
        logging.error(e.message)
        exit(1)

    logging.info(f"Получена сводка.")
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')

    deps = []
    grps = []
    specs = {}

    for rec in data:
        if rec['Факультет'] not in deps:
            deps.append(rec['Факультет'])
        grp = {
            'id': rec['КонкурснаяГруппа'],
            'd_code': rec['Специальность']['КодСпециальности'],
            'd_title': rec['Специальность']['Наименование'],
            'd_level': decode_level(rec['Специальность']['КодСпециальности']),
            'title': rec['Профиль']['Наименование'],
            'base': rec['Профиль']['ОснованиеПоступления'],
            'form': forms.index(rec['ФормаОбучения']) if rec['ФормаОбучения'] in forms else -1,
            'dep': rec['Факультет'],
            'plan': rec['ПланПриема'],
            'cell': rec['КоличествоЦелевыхМест'],
            'podano_budget': rec['ПоданоБюджет'],
            'podano_vnebudget': rec['ПоданоВнебюджет'],
        }
        if rec['Специализация'] is not None and len(rec['Специализация']):
            if grp['id'] not in specs.keys():
                specs[grp['id']] = [rec['Специализация']]
            else:
                specs[grp['id']].append(rec['Специализация'])
        if grp['form'] < 0:
            logging.warning(f'У конкурсной группы {grp["id"]} указана неизвестная форма обучения')
        if min(grp['plan'], grp['cell']) > 0:
            logging.warning(f"Обратить внимание на КЦП: {grp['title']}")
        if min(grp['podano_budget'], grp['podano_vnebudget']) > 0:
            logging.warning(f"Обратить внимание на количество поданных заявлений: {grp['title']}")
        if not any(d.get('id') == grp['id'] for d in grps):
            grps.append(grp)

    logging.info(f'Получено {len(grps)} конкурсных групп: {len(specs.keys())} со специализациями, '
                 f'{sum(len(v) > 1 for _, v in specs.items())} имеют больше одной специализации')
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')

    logging.info(f'Устанавливаю соединение с базой')
    engine = create_engine("mysql+pymysql://root:QWE123321ewq!!!@localhost/sandbox")
    Session = sessionmaker(bind=engine)
    SSUAbit.metadata.create_all(engine)
    s = Session()
    logging.info(f'Соединение с базой установлено. Модели загружены.')
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')

    deps_rows = [Dep(title=t) for t in deps
                 if not len(s.execute(select(Dep).where(Dep.title == t)).all())]
    s.bulk_save_objects(deps_rows)
    s.commit()
    logging.info(f'Загружен справочник подразделений: {len(deps)} получено из 1С, {len(deps_rows)} внесено таблицу.')
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')
    del deps
    del deps_rows
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')

    grps_rows = [Grp(id_grp=g['id'],
                     id_dep=s.execute(
                         select(Dep).where(Dep.title == g['dep'])).first()[0].id_dep,
                     dir_code=g['d_code'], dir_title=g['d_title'], dir_level=g['d_level'],
                     title=g['title'], form=g['form'],
                     plan=g['plan'], cell=g['cell'],
                     podano_budget=g['podano_budget'], podano_vnebudget=g['podano_vnebudget'], )
                 for g in grps
                 if not len(s.execute(select(Grp).where(Grp.id_grp == g['id'])).all())
                 ]

    s.bulk_save_objects(grps_rows)
    s.commit()
    logging.info(f'Загружен справочник конкурсных групп: '
                 f'{len(grps)} получено из 1С, {len(grps_rows)} внесено таблицу.')
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')
    del grps_rows
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')

    specs_rows = []
    for id_grp, titles in specs.items():
        for title in titles:
            specs_rows.append(Spec(id_grp=id_grp, title=title))
    s.bulk_save_objects(specs_rows)
    s.commit()
    logging.info(f'Загружен справочни специализаций.')
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')
    del specs
    del specs_rows
    del srvSvd
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')

    wsdlURL = f'{sources[argSrc]["schema"]}://{sources[argSrc]["host"]}/{sources[argSrc]["base"]}/ws/exam.1cws?wsdl'
    srvExm = Service1C(wsdlURL=wsdlURL,
                       serviceName='ServisVI',
                       portName='ServisVISoap',
                       login=sources[argSrc]['login'],
                       password=sources[argSrc]['password'])
    srvExm.setNamespace('xs', 'http://www.w3.org/2001/XMLSchema')
    srvExm.setNamespace('exam', 'http://portal.sgu.ru/exam')
    logging.info(f"Установлено соединение с сервисом 1С Университет.")
    logging.debug(f'{wsdlURL}; serviceName="ServisVI"; portName="ServisVISoap"')
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')

    xsd_grp_type = srvExm.client.get_type('xs:string')
    xsd_grp_elem = xsd.Element('КодКГ', xsd_grp_type)

    for g in grps:
        val_grp_elem = xsd_grp_elem(g['id'])
        try:
            data = srvExm.client.service.getExam(val_grp_elem)
        except Fault as e:
            logging.error(e.message)
            exit(2)
        logging.info(f"Получены перечни экзаменов для конкурсной группы {g['id']}.")
        logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')
        if len(data) > 1:
            logging.error(f"Пришло больше одной СтрокиКГ (набора) по запросу для одной конкурсной группы")
            exit(1)
        data = data[0]
        exams = []
        id_grp = data['КодКГ']
        for rec in data['НаборВИ']:
            if not len(s.execute(
                    select(Exam).where(
                        Exam.id_grp == id_grp and Exam.subject == rec['ЗаменяемыйПредмет'] and
                        Exam.priority == rec['ПриоритетВступительногоИспытания']
                    )
            ).all()):
                exams.append(
                    Exam(id_grp=id_grp, subject=rec['Предмет'], subject_other=rec['ЗаменяемыйПредмет'],
                         priority=rec['ПриоритетВступительногоИспытания'],
                         )
                )
        s.bulk_save_objects(exams)
        s.commit()
        logging.info(f'Загружен справочник вступительных испытаний для КГ {id_grp}: '
                     f'получено {len(data["НаборВИ"])} наборов, {len(exams)} наборов внесено таблицу.')
        logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')
        del exams
        logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')
    del srvExm
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')

    wsdlURL = f'{sources[argSrc]["schema"]}://{sources[argSrc]["host"]}/{sources[argSrc]["base"]}/ws/spisok.1cws?wsdl'
    srvSps = Service1C(wsdlURL=wsdlURL,
                       serviceName='ServisSpisok',
                       portName='ServisSpisokSoap',
                       login=sources[argSrc]['login'],
                       password=sources[argSrc]['password'])
    srvSps.setNamespace('xs', 'http://www.w3.org/2001/XMLSchema')
    srvSps.setNamespace('abit', 'http://portal.sgu.ru/abit2')
    logging.info(f"Установлено соединение с сервисом 1С Университет.")
    logging.debug(f'{wsdlURL}; serviceName="ServisSpisok"; portName="ServisSpisokSoap"')
    logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')

    xsd_str_type = srvSps.client.get_type('xs:string')
    xsd_getExams_elem = xsd.Element('ПолучатьБаллы', xsd_str_type)
    xsd_getDates_elem = xsd.Element('ПолучатьДатыЗачисления', xsd_str_type)
    xsd_depTitle_elem = xsd.Element('Факультет', xsd_str_type)
    xsd_grpNumbr_elem = xsd.Element('КонкурснаяГруппа', xsd_str_type)

    val_getExams_elem = xsd_getExams_elem(1)
    val_getDates_elem = xsd_getDates_elem(1)
    val_depTitle_elem = xsd_depTitle_elem('?')

    for id_grp in s.execute(select(Grp.id_grp)).all():
        # Это чудо 1С! 000000001 != 1. Поэтому zfill(9)
        val_grpNumbr_elem = xsd_grpNumbr_elem(str(id_grp[0]).zfill(9))
        try:
            data = srvSps.client.service.getSpisok(val_getExams_elem, val_getDates_elem, val_depTitle_elem,
                                                   val_grpNumbr_elem)
        except Fault as e:
            logging.error(e.message)
            exit(3)
        logging.info(f'Получены списки для конкурсной группы {id_grp[0]}.')
        logging.debug(f'Использование памяти {round(psutil.Process().memory_info().rss / 1048576, 2)} Мб')

        exam_points = []
        if not data:
            logging.info(f'В конкурсной группе {id_grp[0]} нет записей об абитуриентах.')
        else:
            logging.info(f'В конкурсной группе {id_grp[0]} найдено {len(data)} записей об абитуриентах.')
            for rec in data:
                res = s.execute(select(Pers).where(Pers.id_pers == rec['Абитуриент']['ВнутреннийКод'])).first()
                if not res:
                    pers = Pers(id_pers=rec['Абитуриент']['ВнутреннийКод'], fio=rec['Абитуриент']['ФИО'])
                    logging.info(f"Абитуриента '{rec['Абитуриент']['ФИО']}' нет в справочнике, добавляем.")
                    s.add(pers)
                    s.commit()
                    res = pers.id_pers
                else:
                    logging.info(f"Абитуриента '{rec['Абитуриент']['ФИО']}' уже есть в справочнике.")
                    res = res[0].id_pers

                sps = Spisok(id_grp=rec['Профиль']['КодПрофиля'],
                             id_pers=res,
                             priority=rec['Приоритет'],  # fixme: Проверить! Возможно 'ПриоритетЗачисления'
                             original=1 if rec['ПоданОригинал'] else 0,
                             returned=1 if rec['Возврат'] else 0,
                             enroll_date=rec['ДатаЗачисления']
                             )
                s.add(sps)
                s.commit()
                res = sps.id_sps
                if rec['Баллы'] is not None:
                    print(rec['Баллы'])
                    for b in rec['Баллы']['Состав']:
                        lvl = s.execute(select(Grp).where(
                            Grp.id_grp == rec['Профиль']['КодПрофиля'])
                        ).first()[0].dir_level
                        ball = ExamPoints(id_sps=res,
                                          exam_priority=
                                          b['Приоритет'] if b['Предмет'] != 'Индивидуальное достижение'
                                          else (2 if lvl == 4 else 4),
                                          subject=b['Предмет'],
                                          points=b['Балл']
                                          )
                        s.add(ball)
                        print(f"Добавил {ball}")
                        s.commit()
