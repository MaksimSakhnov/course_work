# coding: utf-8
from requests import Session
from requests.auth import HTTPBasicAuth
from zeep import Client, Settings
from zeep.transports import Transport
import logging
from requests.exceptions import HTTPError

class Service1C:
    def __init__(self, wsdlURL, serviceName, portName, login=None, password=''):
        session = Session()
        session.verify = False
        if login is not None:
            session.auth = HTTPBasicAuth(login, password)
        try:
            self.client = Client(wsdl=wsdlURL,
                                 transport=Transport(session=session),
                                 service_name=serviceName,
                                 port_name=portName,
                                 settings=Settings(strict=False)
                                 )
        except HTTPError as e:
            logging.error(e)
            exit(10)

    def setNamespace(self, prefix, value):
        self.client.set_ns_prefix(prefix, value)


if __name__ == '__main__':
    pass
    # srv0 = Service1C(wsdlURL='http://cluster1c-1.financial.local/Abit2023/ws/exam.1cws?wsdl',
    #                 serviceName='ServisVI',
    #                 portName='ServisVISoap',
    #                 login='smiabitur',
    #                 password='Qw123321wq')
    # srv0.setNamespace('exam', 'http://portal.sgu.ru/exam')
    # print(srv0.client.service.getExam())

    # srv1 = Service1C(wsdlURL='http://cluster1c-1.financial.local/Abit2023/ws/svodka.1cws?wsdl',
    #                serviceName='ServisSvodka',
    #                portName='ServisSvodkaSoap',
    #                login='smiabitur',
    #                password='Qw123321wq')
    # srv1.setNamespace('abit', 'http://portal.sgu.ru/abit')
    # print(srv1.client.service.getSvodka())

    # srv2 = Service1C(wsdlURL='http://cluster1c-1.financial.local/Abit2023/ws/spisok.1cws?wsdl',
    #                 serviceName='ServisSpisok',
    #                 portName='ServisSpisokSoap',
    #                 login='smiabitur',
    #                 password='Qw123321wq')
    # srv2.setNamespace('abit', 'http://portal.sgu.ru/abit2')
    # print(srv2.client.service.getSpisok())
