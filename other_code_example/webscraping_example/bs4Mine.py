import requests
from bs4 import BeautifulSoup
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import pandas as pd
import time
import pymongo
import re
from datetime import datetime
import logging
import locale

locale.setlocale(locale.LC_ALL, 'en_US.utf8')

class MineTable:

    def __init__(self):
        self.id = 1
        self.url = 'https://analizim.halkyatirim.com.tr/Financial/ScoreCardDetail?hisseKod='
        self.base_url = 'https://analizim.halkyatirim.com.tr/Financial/ScoreCardDetail?hisseKod='
        self.driver = webdriver.Edge()
        self.client = pymongo.MongoClient("mongodb://admin:D1cgnVwYUMT4@157.230.111.171:27017/?authMechanism=DEFAULT")
        self.db = self.client['algofinance']
        self.now = datetime.now()
        f = open('allBistStock.txt')
        text = f.read()
        self.all_bist_stock = text.split(',')
        f.close()
        name_file = open('allBistStockName.txt', encoding="utf8")
        name_text = name_file.read()
        self.all_bist_stock_name = name_text.split(',')
        name_file.close()
        logging.basicConfig(filename='app.log', filemode='a', format='%(name)s - %(levelname)s - %(message)s')

    def start_session(self, code):
        self.code = code
        self.url = self.base_url + code
        self.driver.get(self.url)
        self.driver.maximize_window()
        # Wait for the document to be ready
        try:
            WebDriverWait(self.driver, 15).until(EC.presence_of_element_located((By.ID, 'TBLFINANSALVERİLER1')))
            self.html = self.driver.page_source
        except Exception as e:
            print("Timeout occurred while waiting for the document to be ready.")

    def convert_to_float(self, value):
        try:
            return float(value)
        except:
            try:
                return locale.atof(value)
            except:
                return value

    def pazar_endeksleri(self):
        collection = self.db['FundamentalAnalysis_pazar_endeksleri']

        soup = BeautifulSoup(self.html, 'html.parser')
        div = soup.find(id='pazar-endeskleri')
        table = div.find('table')
        rows = table.find_all("tr")
        data = {}
        data['recor_date'] = self.now
        data['code'] = self.code
        for row in rows:
            cells = row.find_all("td")
            key = cells[0].text.strip()
            value = cells[1].text.strip()

            replaced_name = key.translate(str.maketrans('âğüşıöçĞÜŞİÖÇ', 'agusiocGUSIOC'))
            replaced_name = re.sub(r'[^\w\s]', '_', replaced_name)
            replaced_name = re.sub(r'\s', '_', replaced_name).lower()
            replaced_name = replaced_name.replace('___', '_')
            replaced_name = replaced_name.replace('__', '_')
            replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name
            replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name

            data[replaced_name] = self.convert_to_float(value)

        filter = {'code': self.code}
        response = collection.update_one(filter, {"$set": data}, upsert=True)
        if response.upserted_id is not None:
            logging.info(f'{self.code} Kodlu hissenin Pazar Endeksi Eklendi')
        else:
            logging.info(f'{self.code} Kodlu hissenin Pazar Endeksi Güncellendi')


    def fiyat_performansi(self):
        collection = self.db['FundamentalAnalysis_fiyat_performansi']

        soup = BeautifulSoup(self.html, 'html.parser')
        div = soup.find(id='fiyat-performansi')
        table = div.find('table')
        for row in table.find_all("tr")[1:]:
            data = {}
            data['recor_date'] = self.now
            data["code"] = self.code
            for counter, td in enumerate(row.find_all('td')):
                if counter == 0:
                    data["name"] = td.text.strip()
                elif counter == 1:
                    data["last_week"] = self.convert_to_float(td.text.strip())
                elif counter == 2:
                    data["last_month"] = self.convert_to_float(td.text.strip())
                elif counter == 3:
                    data["last_3_months"] = self.convert_to_float(td.text.strip())
                elif counter == 4:
                    data["last_6_months"] = self.convert_to_float(td.text.strip())
                elif counter == 5:
                    data["last_year"] = self.convert_to_float(td.text.strip())

            filter = {'code': self.code, 'name': data["name"]}
            response = collection.update_one(filter, {"$set": data}, upsert=True)
            if response.upserted_id is not None:
                logging.info(f'{self.code} Kodlu hissenin Fiyat Performansı ({data["name"]}) Eklendi')
            else:
                logging.info(f'{self.code} Kodlu hissenin Fiyat Performansı ({data["name"]}) Güncellendi')

    def piyasa_degeri(self):
        collection = self.db['FundamentalAnalysis_piyasa_degeri']

        soup = BeautifulSoup(self.html, 'html.parser')
        div = soup.find(id='piyasa-degeri')
        table = div.find('table')
        rows = table.find_all("tr")
        data = {}
        data['recor_date'] = self.now
        data['code'] = self.code
        for row in rows:
            cells = row.find_all("td")
            key = cells[0].text.strip()
            value = cells[1].text.strip()

            replaced_name = key.translate(str.maketrans('âğüşıöçĞÜŞİÖÇ', 'agusiocGUSIOC'))
            replaced_name = re.sub(r'[^\w\s]', '_', replaced_name)
            replaced_name = re.sub(r'\s', '_', replaced_name).lower()
            replaced_name = replaced_name.replace('___', '_')
            replaced_name = replaced_name.replace('__', '_')
            replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name
            replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name

            data[replaced_name] = self.convert_to_float(value)

        filter = {'code': self.code}
        response = collection.update_one(filter, {"$set": data}, upsert=True)
        if response.upserted_id is not None:
            logging.info(f'{self.code} Kodlu hissenin Piyasa Değeri Eklendi')
        else:
            logging.info(f'{self.code} Kodlu hissenin Piyasa Değeri Güncellendi')

    def teknik_deger(self):
        soup = BeautifulSoup(self.html, 'html.parser')
        div = soup.find(id='teknik-veriler')
        table = div.find('table')
        rows = table.find_all("tr")
        data = {}
        data['recor_date'] = self.now
        data['code'] = self.code
        for row in rows:
            cells = row.find_all("td")
            key = cells[0].text.strip()
            value = cells[1].text.strip()

            replaced_name = key.translate(str.maketrans('âğüşıöçĞÜŞİÖÇ', 'agusiocGUSIOC'))
            replaced_name = re.sub(r'[^\w\s]', '_', replaced_name)
            replaced_name = re.sub(r'\s', '_', replaced_name).lower()
            replaced_name = replaced_name.replace('___', '_')
            replaced_name = replaced_name.replace('__', '_')
            replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name
            replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name

            data[replaced_name] = self.convert_to_float(value)
        collection = self.db['FundamentalAnalysis_teknik_deger']

        filter = {'code': self.code}
        response = collection.update_one(filter, {"$set": data}, upsert=True)
        if response.upserted_id is not None:
            logging.info(f'{self.code} Kodlu hissenin teknik verileri Eklendi')
        else:
            logging.info(f'{self.code} Kodlu hissenin teknik verileri Güncellendi')


    def temel_analiz_verileri(self):
        soup = BeautifulSoup(self.html, 'html.parser')
        div = soup.find(id='temel-veri-analizleri')
        table = div.find('table')
        rows = table.find_all("tr")
        data = {}
        data['recor_date'] = self.now
        data['code'] = self.code
        for row in rows:
            cells = row.find_all("td")
            key = cells[0].text.strip()
            value = cells[1].text.strip()

            replaced_name = key.translate(str.maketrans('âğüşıöçĞÜŞİÖÇ', 'agusiocGUSIOC'))
            replaced_name = re.sub(r'[^\w\s]', '_', replaced_name)
            replaced_name = re.sub(r'\s', '_', replaced_name).lower()
            replaced_name = replaced_name.replace('___', '_')
            replaced_name = replaced_name.replace('__', '_')
            replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name
            replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name

            data[replaced_name] = self.convert_to_float(value)
        collection = self.db['FundamentalAnalysis_temel_analiz_verileri']

        filter = {'code': self.code}
        response = collection.update_one(filter, {"$set": data}, upsert=True)
        if response.upserted_id is not None:
            logging.info(f'{self.code} Kodlu hissenin temel analiz verileri Eklendi')
        else:
            logging.info(f'{self.code} Kodlu hissenin temel analiz verileri Güncellendi')


    def fiyat_ozeti(self):
        soup = BeautifulSoup(self.html, 'html.parser')
        div = soup.find(id='fiyat-ozeti')
        table = div.find('table')
        rows = table.find_all("tr")
        data = {}
        data['recor_date'] = self.now
        data['code'] = self.code
        for row in rows:
            cells = row.find_all("td")
            key = cells[0].text.strip()
            value = cells[1].text.strip()

            replaced_name = key.translate(str.maketrans('âğüşıöçĞÜŞİÖÇ', 'agusiocGUSIOC'))
            replaced_name = re.sub(r'[^\w\s]', '_', replaced_name)
            replaced_name = re.sub(r'\s', '_', replaced_name).lower()
            replaced_name = replaced_name.replace('___', '_')
            replaced_name = replaced_name.replace('__', '_')
            replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name
            replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name

            data[replaced_name] = self.convert_to_float(value)

        collection = self.db['FundamentalAnalysis_fiyat_ozeti']

        filter = {'code': self.code}
        response = collection.update_one(filter, {"$set": data}, upsert=True)
        if response.upserted_id is not None:
            logging.info(f'{self.code} Kodlu hissenin fiyat özeti verileri Eklendi')
        else:
            logging.info(f'{self.code} Kodlu hissenin fiyat özeti verileri Güncellendi')

    def finansallar(self):
        collection = self.db['FundamentalAnalysis_finansallar']

        soup = BeautifulSoup(self.html, 'html.parser')
        table = soup.find(id='TBLFINANSALVERİLER1')
        columns = table.find_all('th')
        rows = table.find_all('tr')

        for i in rows[1:]:
            counter = 0
            data = {}
            data['recor_date'] = self.now
            data['code'] = self.code
            for column, cell in zip(columns, i.find_all('td')):
                if counter == 0:
                    data['tarih'] = cell.text.replace(' ', '')
                    counter += 1
                else:
                    name = column.text
                    replaced_name = name.translate(str.maketrans('âğüşıöçĞÜŞİÖÇ', 'agusiocGUSIOC'))
                    replaced_name = re.sub(r'[^\w\s]', '_', replaced_name)
                    replaced_name = re.sub(r'\s', '_', replaced_name).lower()
                    replaced_name = replaced_name.replace('___', '_')
                    replaced_name = replaced_name.replace('__', '_')
                    replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name
                    replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name

                    data[replaced_name] = self.convert_to_float(cell.text)

            filter = {'code': self.code, 'tarih': data['tarih']}
            response = collection.update_one(filter, {"$set": data}, upsert=True)
            if response.upserted_id is not None:
                logging.info(f'{self.code} Kodlu hissenin finansallar {data["tarih"]} verileri Eklendi')
            else:
                logging.info(f'{self.code} Kodlu hissenin finansallar {data["tarih"]} verileri Güncellendi')

        

    def karlilik(self):
        collection = self.db['FundamentalAnalysis_karlilik']
        soup = BeautifulSoup(self.html, 'html.parser')
        table = soup.find(id='TBLFINANSALVERİLER2')
        columns = table.find_all('th')
        rows = table.find_all('tr')
        for i in rows[1:]:
            counter = 0
            data = {}
            data['recor_date'] = self.now
            data['code'] = self.code
            for column, cell in zip(columns, i.find_all('td')):
                if counter == 0:
                    data['tarih'] = cell.text
                    counter += 1
                else:
                    name = column.text
                    replaced_name = name.translate(str.maketrans('âğüşıöçĞÜŞİÖÇ', 'agusiocGUSIOC'))
                    replaced_name = re.sub(r'[^\w\s]', '_', replaced_name)
                    replaced_name = re.sub(r'\s', '_', replaced_name).lower()
                    replaced_name = replaced_name.replace('___', '_')
                    replaced_name = replaced_name.replace('__', '_')
                    replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name
                    replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name

                    data[replaced_name] = self.convert_to_float(cell.text)

            filter = {'code': self.code, 'tarih': data['tarih']}
            response = collection.update_one(filter, {"$set": data}, upsert=True)
            if response.upserted_id is not None:
                logging.info(f'{self.code} Kodlu hissenin karlilik {data["tarih"]} verileri Eklendi')
            else:
                logging.info(f'{self.code} Kodlu hissenin karlilik {data["tarih"]} verileri Güncellendi')



    def carpanlar(self):
        collection = self.db['FundamentalAnalysis_carpanlar']
        soup = BeautifulSoup(self.html, 'html.parser')
        table = soup.find(id='TBLFINANSALVERİLER3')
        columns = table.find_all('th')
        rows = table.find_all('tr')

        for i in rows[1:]:
            counter = 0
            data = {}
            data['recor_date'] = self.now
            data['code'] = self.code
            for column, cell in zip(columns, i.find_all('td')):
                if counter == 0:
                    data['tarih'] = cell.text
                    counter += 1
                else:
                    name = column.text
                    replaced_name = name.translate(str.maketrans('âğüşıöçĞÜŞİÖÇ', 'agusiocGUSIOC'))
                    replaced_name = re.sub(r'[^\w\s]', '_', replaced_name)
                    replaced_name = re.sub(r'\s', '_', replaced_name).lower()
                    replaced_name = replaced_name.replace('___', '_')
                    replaced_name = replaced_name.replace('__', '_')
                    replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name
                    replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name

                    data[replaced_name] = self.convert_to_float(cell.text)

            filter = {'code': self.code, 'tarih': data['tarih']}
            response = collection.update_one(filter, {"$set": data}, upsert=True)
            if response.upserted_id is not None:
                logging.info(f'{self.code} Kodlu hissenin carpanlar {data["tarih"]} verileri Eklendi')
            else:
                logging.info(f'{self.code} Kodlu hissenin carpanlar {data["tarih"]} verileri Güncellendi')

    def saveBistStock(self, code, name):
        filter = {'code': code}
        collection = self.db['FundamentalAnalysis_symbols']
        collection.update_one(filter, {"$set": {'code':code, 'name':name, 'recor_date':self.now}}, upsert=True)

if __name__ == '__main__':
    logging.basicConfig(filename='info.log', level=logging.INFO, filemode='a', format='%(name)s - %(levelname)s - %(message)s')
    handler = logging.FileHandler('error.log')
    handler.setLevel(logging.ERROR)
    formatter = logging.Formatter('%(asctime)s %(levelname)s: %(message)s')
    handler.setFormatter(formatter)
    logging.getLogger('').addHandler(handler)

    lastStock = 0
    miner = MineTable()
    """
    counter = 0
    for i in miner.all_bist_stock:
        print(i)
        miner.saveBistStock(i, miner.all_bist_stock_name[counter])
        counter += 1
    """
    try:
        for i in miner.all_bist_stock:
            try:
                miner.start_session(i)
            except Exception as e:
                logging.error(f'{i} kodlu Hisse Alınamadı')
                print(e)
            try:
                miner.pazar_endeksleri()
            except Exception as e:
                logging.error(f'{i} kodlu Hissenin Pazar Endeksi Alınamadı')
                print(e)
            try:
                miner.piyasa_degeri()
            except Exception as e:
                logging.error(f'{i} kodlu Hissenin Piyasa Değeri Alınamadı')
                print(e)

            try:
                miner.fiyat_ozeti()
            except Exception as e:
                logging.error(f'{i} kodlu Hissenin Fiyat Özeti Alınamadı')
                print(e)

            try:
                miner.temel_analiz_verileri()
            except Exception as e:
                logging.error(f'{i} kodlu Hissenin Temel Analiz Verileri Alınamadı')
                print(e)

            try:
                miner.teknik_deger()
            except Exception as e:
                logging.error(f'{i} kodlu Hissenin Teknik Değerleri Alınamadı')
                print(e)

            try:
                miner.fiyat_performansi()
            except Exception as e:
                logging.error(f'{i} kodlu Hissenin Fiyat Performansı Alınamadı')
                print(e)

            try:
                miner.finansallar()
            except Exception as e:
                logging.error(f'{i} kodlu Hissenin Finansalları Alınamadı')
                print(e)

            try:
                miner.karlilik()
            except Exception as e:
                logging.error(f'{i} kodlu Hissenin Karlılıkları Alınamadı')
                print(e)

            try:
                miner.carpanlar()
            except Exception as e:
                logging.error(f'{i} kodlu Hissenin Çarpanları Alınamadı')
                print(e)
    except Exception as e:
        print(e)
