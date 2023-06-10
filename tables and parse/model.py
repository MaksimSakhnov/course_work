# coding: utf-8
from sqlalchemy import Column, Computed, DateTime, ForeignKey, Index, String, TIMESTAMP, text
from sqlalchemy.dialects.mysql import INTEGER
from sqlalchemy.orm import relationship
from sqlalchemy.ext.declarative import declarative_base

SSUAbit = declarative_base()
metadata = SSUAbit.metadata


class Dep(SSUAbit):
    __tablename__ = 'ssu_abit_dep'
    __table_args__ = {'comment': 'Справочник факультетов'}

    id_dep = Column(INTEGER(11), primary_key=True)
    title = Column(String(100), nullable=False, unique=True, comment='Название подразделения (факультета, института)')


class Pers(SSUAbit):
    __tablename__ = 'ssu_abit_pers'
    __table_args__ = {'comment': 'Справочник абитуриентов'}

    id_pers = Column(INTEGER(11), primary_key=True, comment='Идентификатор абитуриента. Исх. поле [ВнутреннийКод]')
    fio = Column(String(250), nullable=False, comment='ФИО абитуриента (но в определённом режиме приходит СНИЛС вместо ФИО, с 2021 года этот режим всегда). Исх. поле [Абитуриент\\ФИО]')


class Grp(SSUAbit):
    __tablename__ = 'ssu_abit_groups'
    __table_args__ = {'comment': 'Таблица конкурсных групп'}

    id_grp = Column(INTEGER(9), primary_key=True, comment='Номер конкурсной группы (КГ). Исх. поле [КонкурснаяГруппа]')
    id_dep = Column(ForeignKey('ssu_abit_dep.id_dep', ondelete='CASCADE', onupdate='CASCADE'), nullable=False, index=True, comment='Внешний ключ на подразделение (факультет, институт)')
    dir_code = Column(String(100), nullable=False, comment='Код направления. Исх. поле [СпециальностьКодСпециальности]')
    dir_title = Column(String(200), nullable=False, comment='Наименование направления. Исх. поле [СпециальностьНаименование]')
    dir_level = Column(INTEGER(2), nullable=False, index=True, comment='Уровень образования: 01, 02, 03 - бакалавриат, 04 - магистратура, 05 - специалитет, 06 - аспирантура. Исх. поле [СпециальностьКодСпециальности]')
    title = Column(String(1000), nullable=False, comment='Полное наименование конкурсной группы. Исх. поле [ПрофильНаименование]')
    form = Column(INTEGER(1), nullable=False, index=True, comment='Форма обучения: 0 - очная, 1 - очно-заочная, 2 - заочная. Исх. поле [ФормаОбучения]')
    plan = Column(INTEGER(5), nullable=False, comment='Места в конкурс. Исх. поле [ПланПриема]')
    cell = Column(INTEGER(5), nullable=False, comment='Количество целевых мест. Исх. поле [КоличествоЦелевыхМест]')
    podano_budget = Column(INTEGER(11), nullable=False, comment='Подано на бюджет. Исх. поле [ПоданоБюджет]')
    podano_vnebudget = Column(INTEGER(11), nullable=False, comment='Подано на внебюджет. Исх. поле [ПоданоВнебюджет]')
    dt = Column(TIMESTAMP, nullable=False, index=True, server_default=text("current_timestamp()"))
    nabor = Column(INTEGER(5), Computed('(greatest(`plan`,`cell`))', persisted=False), comment='Виртуальная колонка - Количество мест набора (обычного или целевого)')
    podano = Column(INTEGER(11), Computed('(greatest(`podano_budget`,`podano_vnebudget`))', persisted=False), comment='Виртуальная колонка - Количество поданных заявлений (по конкурсу или на коммерцию)')

    ssu_abit_dep = relationship('Dep')


class Exam(SSUAbit):
    __tablename__ = 'ssu_abit_exams'
    __table_args__ = (
        Index('uni_exams', 'id_grp', 'subject', 'subject_other', unique=True),
        {'comment': 'Справочник экзаменов конкурсных групп. Он именно справочный! Только для вывода заголовков таблиц (визуала) списков в момент, когда списки пусты или не содержат строки полного спектра вариантов вступительных испытаний'}
    )

    id_exm = Column(INTEGER(11), primary_key=True)
    id_grp = Column(ForeignKey('ssu_abit_groups.id_grp', ondelete='CASCADE', onupdate='CASCADE'), nullable=False, index=True, comment='Номер конкурсной группы (КГ). Исх. поле [КонкурснаяГруппа]')
    subject = Column(String(250), nullable=False, comment='Название предмета. Исх. поле [Предмет]')
    subject_other = Column(String(250), server_default=text("''"), comment='Название второго возможного предмета. Исх. поле [ЗаменяемыйПредмет]')
    priority = Column(INTEGER(3), nullable=False, index=True, comment='Приоритет вступительного испытания. Исх. поле [ПриоритетВступительногоИспытания]')

    ssu_abit_group = relationship('Grp')


class Spec(SSUAbit):
    __tablename__ = 'ssu_abit_specs'
    __table_args__ = {'comment': 'Специализации профилей'}

    id_spec = Column(INTEGER(11), primary_key=True)
    id_grp = Column(ForeignKey('ssu_abit_groups.id_grp', ondelete='CASCADE', onupdate='CASCADE'), nullable=False, index=True, comment='Внешний ключ на конкурсную группу')
    title = Column(String(250), comment='Специализация. Их может быть несколько у одной КГ. Исх. поле [Специализация]')

    ssu_abit_group = relationship('Grp')


class Spisok(SSUAbit):
    __tablename__ = 'ssu_abit_spisok'
    __table_args__ = {'comment': 'Списки абитуриентов по конкурсным группам'}

    id_sps = Column(INTEGER(11), primary_key=True)
    id_grp = Column(ForeignKey('ssu_abit_groups.id_grp', ondelete='CASCADE', onupdate='CASCADE'), nullable=False, index=True, comment='Идентификатор группы')
    id_pers = Column(ForeignKey('ssu_abit_pers.id_pers', ondelete='CASCADE', onupdate='CASCADE'), nullable=False, index=True, comment='Идентификатор абитуриента')
    priority = Column(INTEGER(2), nullable=False, index=True, comment='Приоритет конкурсной группы при зачислении. Исх. поле [Приоритет]')
    original = Column(INTEGER(1), nullable=False, index=True, comment='Подан оригинал: 0 - нет, 1 - да. Исх. поле [ПоданОригинал]')
    returned = Column(INTEGER(1), nullable=False, index=True, comment='Возврат документов: 0 - нет, 1 - да. Исх. поле [Возврат]')
    enroll_date = Column(DateTime, index=True, comment='Дата зачисления')

    ssu_abit_group = relationship('Grp')
    ssu_abit_per = relationship('Pers')


class ExamPoints(SSUAbit):
    __tablename__ = 'ssu_abit_exam_points'
    __table_args__ = {'comment': 'Баллы по экзаменам студента'}

    id_pnts = Column(INTEGER(11), primary_key=True)
    id_sps = Column(ForeignKey('ssu_abit_spisok.id_sps', ondelete='CASCADE', onupdate='CASCADE'), nullable=False, index=True, comment='Идентификатор строки списка')
    exam_priority = Column(INTEGER(2), nullable=False, index=True, comment='Приоритет экзамена')
    subject = Column(String(250), nullable=False, server_default=text("''"), comment='Исключительно только справочное поле! Качественного справочника экзаменов у нас нет')
    points = Column(INTEGER(3), comment='Количество баллов по предмету. Исх. поле [БаллыБалл]')

    ssu_abit_spisok = relationship('Spisok')
