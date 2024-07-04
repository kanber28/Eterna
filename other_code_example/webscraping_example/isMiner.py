from bs4 import BeautifulSoup
from selenium import webdriver
import pandas as pd
import pymongo
import re
from datetime import datetime
import logging

class MineIsYatirim:
    
    def __init__(self):
        self.url = 'https://www.isyatirim.com.tr/tr-tr/analiz/hisse/Sayfalar/sirket-karti.aspx?hisse='
        self.driver = webdriver.Edge()
        
        self.client = pymongo.MongoClient("mongodb://admin:D1cgnVwYUMT4@157.230.111.171:27017/?authMechanism=DEFAULT")
        self.db = self.client['algofinance']

        self.now = datetime.now()

        f = open('allBistStock.txt')
        text = f.read()
        self.all_bist_stock = text.split(',')
        f.close()

    def start_session(self, code):
        self.code = code
        url = self.url + code
        self.driver.get(url)
        self.html_content = self.driver.page_source
        #self.driver.quit()

    def convert_to_float(self, value):
        try:
            return float(value)
        except:
            return value

    def mine_mali_tablo(self):
        collection = self.db['FundamentalAnalysis_mali_tablo']
        soup = BeautifulSoup(self.html_content, 'html.parser')
        tbody = soup.find(id='tbodyMTablo')
        tr_tags = tbody.find_all('tr')
        quarter1 = soup.find(id="select2-ddlMaliTabloDonem1-container")
        quarter2 = soup.find(id="select2-ddlMaliTabloDonem2-container")
        quarter3 = soup.find(id="select2-ddlMaliTabloDonem3-container")
        quarter4 = soup.find(id="select2-ddlMaliTabloDonem4-container")

        quarterArr = [quarter1["title"], quarter2["title"], quarter3["title"], quarter4["title"]]

        columns = []
        quarter1Arr = []
        quarter2Arr = []
        quarter3Arr = []
        quarter4Arr = []
        tmp_name = ''
        for tr in tr_tags:
            td_tags = tr.find_all('td');  
            for key, td in enumerate(td_tags):
                if len(td_tags) == 5:
                    if key == 0:
                        columns.append(tmp_name+td.text)
                    elif key == 1:
                        quarter1Arr.append(td.text)
                    elif key == 2:
                        quarter2Arr.append(td.text)
                    elif key == 3:
                        quarter3Arr.append(td.text)
                    elif key == 4:
                        quarter4Arr.append(td.text)
                else:
                    tmp_name = td.text + '_'
        
        resultArr = []
        for i in range(4):
            data = {}
            data['tarih'] = quarterArr[i]
            data['code'] = self.code
            data['record_date'] = self.now
            for counter, column in enumerate(columns):
                    replaced_name = column.translate(str.maketrans('âğüşıöçĞÜŞİÖÇ', 'agusiocGUSIOC'))
                    replaced_name = re.sub(r'[^\w\s]', '_', replaced_name)
                    replaced_name = re.sub(r'\s', '_', replaced_name).lower()
                    replaced_name = replaced_name.replace('___', '_')
                    replaced_name = replaced_name.replace('__', '_')
                    replaced_name = replaced_name[:-1] if replaced_name[-1] == '_' else replaced_name
                    replaced_name = replaced_name[1:] if replaced_name[0] == '_' else replaced_name
                    replaced_name = replaced_name.replace('__', '_')

                    if replaced_name in data:
                        replaced_name = replaced_name + '_2'
                        if replaced_name in data:
                            replaced_name = replaced_name + '_3'

                    if i == 0:
                        data[replaced_name] = self.convert_to_float(quarter1Arr[counter]) 
                    elif i == 1:
                        data[replaced_name] = self.convert_to_float(quarter2Arr[counter]) 
                    elif i == 2:
                        data[replaced_name] = self.convert_to_float(quarter3Arr[counter]) 
                    elif i == 3:
                        data[replaced_name] = self.convert_to_float(quarter4Arr[counter]) 

            resultArr.append(data)

            filter = {'code': self.code, 'tarih': data['tarih']}
            response = collection.update_one(filter, {"$set": data}, upsert=True)
            if response.upserted_id is not None:
                logging.info(f'{self.code} Kodlu hissenin Mali {data["tarih"]} verileri Eklendi')
            else:
                logging.info(f'{self.code} Kodlu hissenin Mali {data["tarih"]} verileri Güncellendi')
        
    

if __name__ == "__main__":
    miner = MineIsYatirim()

    stocks = miner.all_bist_stock

    for code in stocks:
        try:
            miner.start_session(code)
            miner.mine_mali_tablo()
        except:
            print(code)
