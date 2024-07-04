from django.shortcuts import render, HttpResponse
from django.http import JsonResponse
from .models import *
import yfinance as yf
from datetime import date, timedelta
import json
import pandas as pd
from django.contrib.auth.decorators import login_required
from FundamentalAnalysisApi import serializers
from django.core.cache import cache
import datetime
import pandas as pd

# @login_required
def index(request):
    return render(request, 'FundamentalAnalysis/index.html')

def get_stock_table(request):
    cache_key = "stock_table"
    cache_time = 86400
    values = cache.get(cache_key)
    if values is None:
        stocks = Symbols.objects.order_by('code').all()    

        values = []
        for item in stocks:
            code = item.code
            stockEndeks =  Pazar_Endeksleri.objects.filter(code=code).first()
            if stockEndeks is not None:
                sector = stockEndeks.dahil_oldugu_sektor
            else:
                sector = '-'
            data = {
                'code':item.code,
                'sector':sector,
                'name': item.name,
                'detail': '<a href="./?code='+item.code+'" class="btn btn-primary" >Detay</a>'
            }
            values.append(data)
        cache.set(cache_key, values, cache_time)

    return JsonResponse({'data': values})


def autocomplate(request):
    query = request.GET.get('query')
    query = query.upper()
    results = Symbols.objects.filter(code__icontains=query).order_by('code').values('code')[0:8]
    suggestions = [item['code'] for item in results]

    return JsonResponse({'suggestions': suggestions})

def sectorelAnalyze(request):
    categories = Pazar_Endeksleri.objects.values_list('dahil_oldugu_sektor', flat=True).distinct().order_by('dahil_oldugu_sektor')

    return render(request, 'FundamentalAnalysis/sectoralAnalysis.html', {'categories':categories})


def get_live_price(request):
    stock_code = request.GET.get('code') + '.IS'
    yesterday = datetime.datetime.today() - datetime.timedelta(days=1)

    stock = yf.download(stock_code, start=yesterday.strftime('%Y-%m-%d'), interval='5m')

    price = stock.iloc[-1].Close
    return JsonResponse({'price':float(price)})


def get_sector_average(request):
    sector = request.GET.get('sector')
    sector = sector.replace(' ', '_')
    turkish_chars = "çğıiİıöşüÇĞİIÖŞÜ"
    english_chars = "cgiiIioSuCGIIOSU"
    translation_dict = str.maketrans(turkish_chars, english_chars)
    translated_sector = sector.translate(translation_dict)
    cache_key = "sectoral_average_cache__" + translated_sector
    cache_time = 86400
    data = cache.get(cache_key)
    if data is None:
        if request.GET.get('sector') != "0":
            sector = request.GET.get('sector')
            stocks = Pazar_Endeksleri.objects.filter(dahil_oldugu_sektor=sector).all()
            stockList = []
            for item in stocks:
                code = item.code
                fundamentalAnalysisData = Temel_Analiz_Verileri.objects.filter(code=code).order_by('-record_date').first()
                marketValue = Piyasa_Degeri.objects.filter(code=code).order_by('-record_date').first()
                if fundamentalAnalysisData is not None:
                    data = {
                        'code':item.code,
                        'f_k': 0 if type(fundamentalAnalysisData.f_k) == str else fundamentalAnalysisData.f_k,
                        'fd_favok': 0 if type(fundamentalAnalysisData.fd_favok) == str else fundamentalAnalysisData.fd_favok,
                        'pd_dd': 0 if type(fundamentalAnalysisData.pd_dd) == str else fundamentalAnalysisData.pd_dd,
                        'net_kar_buyume_orani': 0 if type(fundamentalAnalysisData.net_kar_buyume_orani) == str else fundamentalAnalysisData.net_kar_buyume_orani,
                        'halka_aciklik_orani':item.halka_aciklik_orani,
                        'piyasa_degeri_mln':marketValue.piyasa_degeri_mln,
                    }
                    stockList.append(data)
            
            stockListDf = pd.DataFrame(stockList)

            cleanFK = stockListDf[stockListDf['f_k'] != 0]
            cleanFK = cleanFK[stockListDf['f_k'] < 100] #100 fk üstünü anlamsız kabul edip işlemden çıkaralım
            averageFK = cleanFK['f_k'].mean()

            cleanFDFAVOK = stockListDf[stockListDf['fd_favok'] != 0] 
            cleanFDFAVOK = cleanFDFAVOK[stockListDf['fd_favok'] < 100] #100 fdfavök üstünü anlamsız kabul edip işlemden çıkaralım
            averageFDFAVOK = cleanFDFAVOK['fd_favok'].mean()

            cleanPDDD = stockListDf[stockListDf['pd_dd'] != 0]
            cleanPDDD = cleanPDDD[stockListDf['pd_dd'] < 100] #100 pd dd üstünü anlamsız kabul edip işlemden çıkaralım
            averagePDDD = cleanPDDD['pd_dd'].mean()

            withoutZeroKarBuyume = stockListDf[stockListDf['net_kar_buyume_orani'] != 0]
            averageKarBuyumeOrani = withoutZeroKarBuyume['net_kar_buyume_orani'].mean()
            result = {'data':{'averageFk' : round(averageFK, 2), 'averagePDDD' : round(averagePDDD, 2), 'averageFDFAVOK':round(averageFDFAVOK,2), 'averageKarBuyumeOrani': round(averageKarBuyumeOrani, 2)}}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse({'data':{'averageFk' : "-", 'averagePDDD' : "-", 'averageFDFAVOK':"-", 'averageKarBuyumeOrani': "-"}})
    else:
        return JsonResponse(data)

def get_sector_table(request):
    sector = request.GET.get('sector')
    sector = sector.replace(' ', '_')
    turkish_chars = "çğıiİıöşüÇĞİIÖŞÜ"
    english_chars = "cgiiIioSuCGIIOSU"
    translation_dict = str.maketrans(turkish_chars, english_chars)
    translated_sector = sector.translate(translation_dict)
    cache_key = "sectoral_table_cache__" + translated_sector
    cache_time = 86400
    stockList = cache.get(cache_key)
    if stockList is None:
        if request.GET.get('sector') != "0":
            sector = request.GET.get('sector')
            stocks = Pazar_Endeksleri.objects.filter(dahil_oldugu_sektor=sector).all()
            stockList = []
            for item in stocks:
                code = item.code
                fundamentalAnalysisData = Temel_Analiz_Verileri.objects.filter(code=code).order_by('-record_date').first()
                marketValue = Piyasa_Degeri.objects.filter(code=code).order_by('-record_date').first()
                if fundamentalAnalysisData is not None:
                    freeFloatRatio = item.halka_aciklik_orani
                    freeFloatRatio = freeFloatRatio.replace(' ', '').replace('%', '')
                    data = {
                        'code':item.code,
                        'f_k':fundamentalAnalysisData.f_k,
                        'fd_favok': fundamentalAnalysisData.fd_favok,
                        'pd_dd': fundamentalAnalysisData.pd_dd,
                        'net_kar_buyume_orani': fundamentalAnalysisData.net_kar_buyume_orani,
                        'halka_aciklik_orani':freeFloatRatio,
                        'piyasa_degeri_mln':marketValue.piyasa_degeri_mln,
                        'hisse_basina_kar' : fundamentalAnalysisData.hisse_basina_kar,
                        'temettu_verimi' : fundamentalAnalysisData.temettu_verimi,
                        'detail': '<a href="./?code='+item.code+'" class="btn btn-primary" >Detay</a>'
                    }
                    stockList.append(data)
            cache.set(cache_key, stockList, cache_time)
        elif request.GET.get('sector') == "0":
            stocks = Pazar_Endeksleri.objects.all()
            stockList = []
            for item in stocks:
                code = item.code
                fundamentalAnalysisData = Temel_Analiz_Verileri.objects.filter(code=code).order_by('-record_date').first()
                marketValue = Piyasa_Degeri.objects.filter(code=code).order_by('-record_date').first()
                if fundamentalAnalysisData is not None:
                    freeFloatRatio = item.halka_aciklik_orani
                    freeFloatRatio = freeFloatRatio.replace(' ', '').replace('%', '')
                    data = {
                        'code':item.code,
                        'f_k':fundamentalAnalysisData.f_k,
                        'fd_favok': fundamentalAnalysisData.fd_favok,
                        'pd_dd': fundamentalAnalysisData.pd_dd,
                        'net_kar_buyume_orani': fundamentalAnalysisData.net_kar_buyume_orani,
                        'halka_aciklik_orani':freeFloatRatio,
                        'piyasa_degeri_mln':marketValue.piyasa_degeri_mln,
                        'hisse_basina_kar' : fundamentalAnalysisData.hisse_basina_kar,
                        'temettu_verimi' : fundamentalAnalysisData.temettu_verimi,
                        'detail': '<a href="./?code='+item.code+'" class="btn btn-primary" >Detay</a>'
                    }
                    stockList.append(data)
            cache.set(cache_key, stockList, cache_time)

        return JsonResponse({'data':stockList})
    else:    
        return JsonResponse({'data':stockList})


def stock_filter(request):
    return render(request, 'FundamentalAnalysis/stockFilter.html')


def get_stock_filter_table(request):
    cache_key = "stock_filter_table"
    cache_time = 86400
    stockList = cache.get(cache_key)
    if stockList is None:
        stocks = Pazar_Endeksleri.objects.all()
        stockList = []
        for item in stocks:
            code = item.code
            sector = Pazar_Endeksleri.objects.filter(code = code).first()
            fundamentalAnalysisData = Temel_Analiz_Verileri.objects.filter(code=code).order_by('-record_date').first()
            marketValue = Piyasa_Degeri.objects.filter(code=code).order_by('-record_date').first()
            if fundamentalAnalysisData is not None:
                freeFloatRatio = item.halka_aciklik_orani
                freeFloatRatio = freeFloatRatio.replace(' ', '').replace('%', '')
                data = {
                    'code':item.code,
                    'sector':sector.dahil_oldugu_sektor,
                    'f_k':fundamentalAnalysisData.f_k,
                    'fd_favok': fundamentalAnalysisData.fd_favok,
                    'pd_dd': fundamentalAnalysisData.pd_dd,
                    'net_kar_buyume_orani': fundamentalAnalysisData.net_kar_buyume_orani,
                    'halka_aciklik_orani':freeFloatRatio,
                    'piyasa_degeri_mln':marketValue.piyasa_degeri_mln,
                    'hisse_basina_kar' : fundamentalAnalysisData.hisse_basina_kar,
                    'temettu_verimi' : fundamentalAnalysisData.temettu_verimi,
                    'detail': '<a href="./?code='+item.code+'" class="btn btn-primary" >Detay</a>'
                }
                stockList.append(data)
        cache.set(cache_key, stockList, cache_time)
    return JsonResponse({'data':stockList})  

# The summary function returns fundamental summary page with data 
def summary(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()
        stockName = Symbols.objects.filter(code=code).first()
        stockEndex = Pazar_Endeksleri.objects.filter(code=code).order_by('-record_date').first()
        fundamentalAnalysisData = Temel_Analiz_Verileri.objects.filter(code=code).order_by('-record_date').first()
        marketValue = Piyasa_Degeri.objects.filter(code=code).order_by('-record_date').first()
        pricePerformance = Fiyat_Performansi.objects.filter(code=code).all()
        technicalData = Teknik_Deger.objects.filter(code=code).order_by('-record_date').first()
        priceSummary = Fiyat_Ozeti.objects.filter(code=code).order_by('-record_date').first()
        if stockEndex is not None:
            ratioText = stockEndex.halka_aciklik_orani
            freeFloatRatio = float(ratioText.replace(' ', '').replace('%', ''))
        else:
            freeFloatRatio = 0
    
        
        notes = {
            "net_kar": "",
            "satis": "",
            "favok": "",
            "ozkaynak": "",
        }
        financial = Finansallar.objects.filter(code=code).order_by('-tarih')[:8]
        last_quarter = financial[2]
        second_quarter = financial[3]
        last_year = financial[6]
        difference_year = financial[1] 
        difference_quarter = financial[0] 
        try:
            ratio = ((financial[2].net_kar_ceyrek_mln_tl - financial[3].net_kar_ceyrek_mln_tl) * abs(financial[3].net_kar_ceyrek_mln_tl)) * 100
            notes["net_kar_ratio"] = ratio

            if financial[2].net_kar_ceyrek_mln_tl > financial[3].net_kar_ceyrek_mln_tl:
                notes["net_kar"] = "positive"
            else:
                notes["net_kar"] = "negative"
        except:
            notes["net_kar"] = None
        try:
            ratio = ((financial[2].favok_ceyrek_mln_tl - financial[3].favok_ceyrek_mln_tl) * abs(financial[3].favok_ceyrek_mln_tl)) * 100
            notes["favok_ratio"] = ratio

            if financial[2].favok_ceyrek_mln_tl > financial[3].favok_ceyrek_mln_tl:
                notes["favok"] = "positive"
            else:
                notes["favok"] = "negative"
        except:
            notes["favok"] = None
        
        try:
            ratio = ((financial[2].ozkaynaklar_mln_tl - financial[3].ozkaynaklar_mln_tl) * abs(financial[3].ozkaynaklar_mln_tl)) * 100
            notes["ozkaynak_ratio"] = ratio

            if financial[2].ozkaynaklar_mln_tl > financial[3].ozkaynaklar_mln_tl:
                notes["ozkaynak"] = "positive"
            else:
                notes["ozkaynak"] = "negative"
        except:
            notes["ozkaynak"] = None
        try:
            ratio = ((financial[2].net_satislar_ceyrek_mln_tl - financial[3].net_satislar_ceyrek_mln_tl) * abs(financial[3].net_satislar_ceyrek_mln_tl)) * 100
            notes["satis_ratio"] = ratio

            if financial[2].net_satislar_ceyrek_mln_tl > financial[3].net_satislar_ceyrek_mln_tl:
                notes["satis"] = "positive"
            else:
                notes["satis"] = "negative"
        except:
            notes["satis"] = None

    else:
        stockEndex = None
        code = None
        fundamentalAnalysisData = None
        marketValue = None
        pricePerformance = None
        technicalData = None
        priceSummary = None
        freeFloatRatio = None
        stockName = None

    
    if stockEndex.dahil_oldugu_sektor == 'Banka':
        return render(request, 'FundamentalAnalysis/summaryBank.html', {'endexs':stockEndex, 'code':code, 'fundamentalData':fundamentalAnalysisData, 'marketValue':marketValue,
                                              'pricePerformance':pricePerformance, 'technicalData':technicalData, 'priceSummary':priceSummary, 
                                              'freeFloatRatio':freeFloatRatio, 'name':stockName, 'notes':notes, 'financials':financial, 'last_quarter':last_quarter, 'last_year':last_year, 'difference_quarter':difference_quarter, 'difference_year':difference_year, 'second_quarter':second_quarter})
    else:
        return render(request, 'FundamentalAnalysis/summary.html', {'endexs':stockEndex, 'code':code, 'fundamentalData':fundamentalAnalysisData, 'marketValue':marketValue,
                                              'pricePerformance':pricePerformance, 'technicalData':technicalData, 'priceSummary':priceSummary, 
                                              'freeFloatRatio':freeFloatRatio, 'name':stockName, 'notes':notes, 'financials':financial, 'last_quarter':last_quarter, 'last_year':last_year, 'difference_quarter':difference_quarter, 'difference_year':difference_year, 'second_quarter':second_quarter})



def specialSearchIndex(request):
    return render(request, 'FundamentalAnalysis/specialAnalysis.html')

def specialSearch(request):
    searchType = request.GET.get('type')

    if (searchType == "0"): #Bir önceki çeyreğe göre karı artanlar
        symbols = Symbols.objects.all()
        searchResult = []
        for code in symbols:
            financial = Finansallar.objects.filter(code=code.code).order_by('-tarih')[:4]

            try:
                if financial[2].net_kar_ceyrek_mln_tl > financial[3].net_kar_ceyrek_mln_tl:
                    serializedSymbols = serializers.SymbolsSerializer(code)
                    searchResult.append(serializedSymbols.data)
            except:
                continue

        return JsonResponse({'result': searchResult})
    
    return JsonResponse({'result': "searchResult"})
    

def possitiveNegativeScore(request):
    code = request.GET.get('code')
    notes = {
        "net_kar": "",
        "satis": "",
        "favok": "",
        "ozkaynak": "",
    }
    financial = Finansallar.objects.filter(code=code.code).order_by('-tarih')[:2]
    if financial[0].net_kar_ceyrek_mln_tl > financial[1].net_kar_ceyrek_mln_tl:
        notes["net_kar"] = "possitive"
    else:
        notes["net_kar"] = "negative"

    if financial[0].favok_ceyrek_mln_tl > financial[1].favok_ceyrek_mln_tl:
        notes["favok"] = "possitive"
    else:
        notes["favok"] = "negative"

    if financial[0].ozkaynaklar_mln_tl > financial[1].ozkaynaklar_mln_tl:
        notes["ozkaynak"] = "possitive"
    else:
        notes["ozkaynak"] = "negative"

    if financial[0].net_satislar_ceyrek_mln_tl > financial[1].net_satislar_ceyrek_mln_tl:
        notes["satis"] = "possitive"
    else:
        notes["satis"] = "negative"

def get_stock_notes_for_general(request):

    if request.GET.get('code') is not None:
        cache_key = "stocks_note_for_general__" + request.GET.get('code').upper()
        cache_time = 86400
        values = cache.get(cache_key)
        if values is None:
            code = request.GET.get('code').upper()
            symbol = Symbols.objects.filter(code=code).order_by('-record_date').first()
            stock_data = Temel_Analiz_Verileri.objects.filter(code=code).order_by('-record_date').first()

            fundamental_datas = Temel_Analiz_Verileri.objects.all()

            sorted_by_fk = fundamental_datas.order_by('f_k')
            sorted_by_pd_dd = fundamental_datas.order_by('pd_dd')
            sorted_by_fd_favok = fundamental_datas.order_by('fd_favok')

            fk_df = pd.DataFrame(sorted_by_fk.values())
            fk = fk_df[fk_df['code'] == symbol.code]
            fk_rank = fk.index[0] + 1

            pd_dd_df = pd.DataFrame(sorted_by_pd_dd.values())
            pd_dd = pd_dd_df[pd_dd_df['code'] == symbol.code]
            pd_dd_rank = pd_dd.index[0] + 1

            fd_favok_df = pd.DataFrame(sorted_by_fd_favok.values())
            fd_favok = fd_favok_df[fd_favok_df['code'] == symbol.code]
            fd_favok_rank = fd_favok.index[0] + 1

            total_record = fundamental_datas.count()
            result = {'fk_rank':str(fk_rank), 'pd_dd_rank':str(pd_dd_rank), 'fd_favok_rank':str(fd_favok_rank), 'total_record':total_record}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(values)
    

def get_stock_notes_for_sector(request):
    if request.GET.get('code') is not None:
        cache_key = "stocks_note_for_sector__" + request.GET.get('code').upper()
        cache_time = 86400
        values = cache.get(cache_key)
        if values is None:
            code = request.GET.get('code').upper()
            symbol = Symbols.objects.filter(code=code).order_by('-record_date').first()
            stock_data = Temel_Analiz_Verileri.objects.filter(code=code).order_by('-record_date').first()
            endeks = Pazar_Endeksleri.objects.filter(code=code).order_by('-record_date').first()

            sector = endeks.dahil_oldugu_sektor

            sector_stocks = Pazar_Endeksleri.objects.filter(dahil_oldugu_sektor=sector).all()

            stock_codes_df = pd.DataFrame(sector_stocks.values('code'))
            stock_codes = stock_codes_df['code'].tolist()

            fundamental_datas = Temel_Analiz_Verileri.objects.filter(code__in=stock_codes).order_by('-record_date').all()

            sorted_by_fk = fundamental_datas.order_by('f_k')
            sorted_by_pd_dd = fundamental_datas.order_by('pd_dd')
            sorted_by_fd_favok = fundamental_datas.order_by('fd_favok')

            fk_df = pd.DataFrame(sorted_by_fk.values())
            fk = fk_df[fk_df['code'] == symbol.code]
            fk_rank = fk.index[0] + 1

            pd_dd_df = pd.DataFrame(sorted_by_pd_dd.values())
            pd_dd = pd_dd_df[pd_dd_df['code'] == symbol.code]
            pd_dd_rank = pd_dd.index[0] + 1

            fd_favok_df = pd.DataFrame(sorted_by_fd_favok.values())
            fd_favok = fd_favok_df[fd_favok_df['code'] == symbol.code]
            fd_favok_rank = fd_favok.index[0] + 1

            total_record = fundamental_datas.count()
            result = {'fk_rank':str(fk_rank), 'pd_dd_rank':str(pd_dd_rank), 'fd_favok_rank':str(fd_favok_rank), 'total_record':total_record}
            cache.set(cache_key, values, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(values)
        

def getFinancialData(request):
    today = date.today()
    yesterday = today - timedelta(days = 1)
    bist100 = yf.download("XU100.IS", start=today)


def get_financial_statment(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')

        financial_statment = Mali_Tablo.objects.filter(code=code).order_by('-tarih').all()[:4]
        sector = Pazar_Endeksleri.objects.filter(code=code).first()
        if sector.dahil_oldugu_sektor == 'Banka':
            return render(request, 'FundamentalAnalysis/financialStatmentBank.html', {'financial_statment' : financial_statment, 'code' : code})
        else:
            return render(request, 'FundamentalAnalysis/financialStatment.html', {'financial_statment' : financial_statment, 'code' : code})
    


#the get chart functions which after this line for get financial charts data with length option. The functions return json response to us

def get_fk_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "fk_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Carpanlar.objects.filter(code=code).order_by('-tarih').all()[:size]
            fk_values = []
            fk_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.f_k, float):
                        fk_values.append(item.f_k)
                    else:
                        fk_values.append(0)
                    fk_labels.append(item.tarih)
            fk_labels = fk_labels[::-1]
            fk_values = fk_values[::-1]

            fk_colors = []
            temp_value = 0
            counter = 0
            for i in fk_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    fk_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value > i:
                        fk_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        fk_colors.append("#00FF00") #Yeşil
                        temp_value = i
                    else:
                        fk_colors.append("#FF0000") #Kırmızı
                        temp_value = i

            result = {'values':fk_values, 'labels':fk_labels, 'colors':fk_colors}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_fd_favok_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "fd_favok_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Carpanlar.objects.filter(code=code).order_by('-tarih').all()[:size]
            fd_favok_values = []
            fd_favok_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    fd_favok_values.append(item.fd_favok)
                    fd_favok_labels.append(item.tarih)

            fd_favok_labels = fd_favok_labels[::-1]
            fd_favok_values = fd_favok_values[::-1]


            fd_favok_colors = []
            temp_value = 0
            counter = 0
            for i in fd_favok_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    fd_favok_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value > i:
                        fd_favok_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        fd_favok_colors.append("#00FF00") #Yeşil
                        temp_value = i
                    else:
                        fd_favok_colors.append("#FF0000") #Kırmızı
                        temp_value = i

            result = {'values':fd_favok_values, 'labels':fd_favok_labels, 'colors':fd_favok_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_pd_dd_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "pd_dd_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Carpanlar.objects.filter(code=code).order_by('-tarih').all()[:size]
            pd_dd_values = []
            pd_dd_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    pd_dd_values.append(item.pd_dd)
                    pd_dd_labels.append(item.tarih)

            pd_dd_labels = pd_dd_labels[::-1]
            pd_dd_values = pd_dd_values[::-1]


            pd_dd_colors = []
            temp_value = 0
            counter = 0
            for i in pd_dd_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    pd_dd_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value > i:
                        pd_dd_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        pd_dd_colors.append("#00FF00") #Yeşil
                        temp_value = i
                    else:
                        pd_dd_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':pd_dd_values, 'labels':pd_dd_labels, 'colors':pd_dd_colors}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_fd_satislar_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "fd_satisler_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)

        if cached_value is None:

            values = Carpanlar.objects.filter(code=code).order_by('-tarih').all()[:size]
            fd_satislar_values = []
            fd_satislar_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    fd_satislar_values.append(item.fd_satislar)
                    fd_satislar_labels.append(item.tarih)

            fd_satislar_labels = fd_satislar_labels[::-1]
            fd_satislar_values = fd_satislar_values[::-1]


            fd_satislar_colors = []
            temp_value = 0
            counter = 0
            for i in fd_satislar_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    fd_satislar_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value > i:
                        fd_satislar_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        fd_satislar_colors.append("#00FF00") #Yeşil
                        temp_value = i
                    else:
                        fd_satislar_colors.append("#FF0000") #Kırmızı
                        temp_value = i

            result = {'values':fd_satislar_values, 'labels':fd_satislar_labels, 'colors':fd_satislar_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})
            

def get_piyasa_degeri_mln_tl_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "piyasa_degeri_mln_tl_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Carpanlar.objects.filter(code=code).order_by('-tarih').all()[:size]
            piyasa_degeri_mln_tl_values = []
            piyasa_degeri_mln_tl_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    piyasa_degeri_mln_tl_values.append(item.piyasa_degeri_mln_tl)
                    piyasa_degeri_mln_tl_labels.append(item.tarih)

            piyasa_degeri_mln_tl_labels = piyasa_degeri_mln_tl_labels[::-1]
            piyasa_degeri_mln_tl_values = piyasa_degeri_mln_tl_values[::-1]


            piyasa_degeri_mln_tl_colors = []
            temp_value = 0
            counter = 0
            for i in piyasa_degeri_mln_tl_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    piyasa_degeri_mln_tl_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        piyasa_degeri_mln_tl_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        piyasa_degeri_mln_tl_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        piyasa_degeri_mln_tl_colors.append("#FF0000") #Kırmızı
                        temp_value = i

            result = {'values':piyasa_degeri_mln_tl_values, 'labels':piyasa_degeri_mln_tl_labels, 'colors':piyasa_degeri_mln_tl_colors}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_hisse_basina_kar_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "hisse_basina_kar_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Carpanlar.objects.filter(code=code).order_by('-tarih').all()[:size]
            hisse_basina_kar_values = []
            hisse_basina_kar_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    hisse_basina_kar_values.append(item.hisse_basi_kar)
                    hisse_basina_kar_labels.append(item.tarih)

            hisse_basina_kar_labels = hisse_basina_kar_labels[::-1]
            hisse_basina_kar_values = hisse_basina_kar_values[::-1]


            hisse_basina_kar_colors = []
            temp_value = 0
            counter = 0
            for i in hisse_basina_kar_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    hisse_basina_kar_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        hisse_basina_kar_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        hisse_basina_kar_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        hisse_basina_kar_colors.append("#FF0000") #Kırmızı
                        temp_value = i

            result = {'values':hisse_basina_kar_values, 'labels':hisse_basina_kar_labels, 'colors':hisse_basina_kar_colors}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_hisse_basina_temettu_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "hisse_basina_temettu_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Carpanlar.objects.filter(code=code).order_by('-tarih').all()[:size]
            hisse_basina_temettu_values = []
            hisse_basina_temettu_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    hisse_basina_temettu_values.append(item.hisse_basi_temettu)
                    hisse_basina_temettu_labels.append(item.tarih)

            hisse_basina_temettu_labels = hisse_basina_temettu_labels[::-1]
            hisse_basina_temettu_values = hisse_basina_temettu_values[::-1]


            hisse_basina_temettu_colors = []
            temp_value = 0
            counter = 0
            for i in hisse_basina_temettu_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    hisse_basina_temettu_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        hisse_basina_temettu_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        hisse_basina_temettu_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        hisse_basina_temettu_colors.append("#FF0000") #Kırmızı
                        temp_value = i

            result = {'values':hisse_basina_temettu_values, 'labels':hisse_basina_temettu_labels, 'colors':hisse_basina_temettu_colors}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_stok_tutma_suresi_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "stok_tutma_suresi_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Carpanlar.objects.filter(code=code).order_by('-tarih').all()[:size]
            stok_tutma_suresi_gun_values = []
            stok_tutma_suresi_gun_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    stok_tutma_suresi_gun_values.append(item.stok_tutma_suresi_gun)
                    stok_tutma_suresi_gun_labels.append(item.tarih)

            stok_tutma_suresi_gun_labels = stok_tutma_suresi_gun_labels[::-1]
            stok_tutma_suresi_gun_values = stok_tutma_suresi_gun_values[::-1]


            stok_tutma_suresi_gun_colors = []
            temp_value = 0
            counter = 0
            for i in stok_tutma_suresi_gun_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    stok_tutma_suresi_gun_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value > i:
                        stok_tutma_suresi_gun_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        stok_tutma_suresi_gun_colors.append("#00FF00") #Yeşil
                        temp_value = i
                    else:
                        stok_tutma_suresi_gun_colors.append("#FF0000") #Kırmızı
                        temp_value = i

            result = {'values':stok_tutma_suresi_gun_values, 'labels':stok_tutma_suresi_gun_labels, 'colors':stok_tutma_suresi_gun_colors}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})


def get_alacak_tahsil_suresi_gun_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "alacak_tahsil_suresi_gun_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Carpanlar.objects.filter(code=code).order_by('-tarih').all()[:size]
            alacak_tahsil_suresi_gun_values = []
            alacak_tahsil_suresi_gun_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    alacak_tahsil_suresi_gun_values.append(item.alacak_tahsil_suresi_gun)
                    alacak_tahsil_suresi_gun_labels.append(item.tarih)

            alacak_tahsil_suresi_gun_labels = alacak_tahsil_suresi_gun_labels[::-1]
            alacak_tahsil_suresi_gun_values = alacak_tahsil_suresi_gun_values[::-1]


            alacak_tahsil_suresi_gun_colors = []
            temp_value = 0
            counter = 0
            for i in alacak_tahsil_suresi_gun_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    alacak_tahsil_suresi_gun_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value > i:
                        alacak_tahsil_suresi_gun_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        alacak_tahsil_suresi_gun_colors.append("#00FF00") #Yeşil
                        temp_value = i
                    else:
                        alacak_tahsil_suresi_gun_colors.append("#FF0000") #Kırmızı
                        temp_value = i

            result = {'values':alacak_tahsil_suresi_gun_values, 'labels':alacak_tahsil_suresi_gun_labels, 'colors':alacak_tahsil_suresi_gun_colors}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_borc_tahsil_suresi_gun_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "borc_tahsil_suresi_gun_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)

        if cached_value is None:
            values = Carpanlar.objects.filter(code=code).order_by('-tarih').all()[:size]
            borc_tahsil_suresi_gun_values = []
            borc_tahsil_suresi_gun_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    borc_tahsil_suresi_gun_values.append(item.borc_odeme_suresi_gun)
                    borc_tahsil_suresi_gun_labels.append(item.tarih)

            borc_tahsil_suresi_gun_labels = borc_tahsil_suresi_gun_labels[::-1]
            borc_tahsil_suresi_gun_values = borc_tahsil_suresi_gun_values[::-1]


            borc_tahsil_suresi_gun_colors = []
            temp_value = 0
            counter = 0
            for i in borc_tahsil_suresi_gun_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    borc_tahsil_suresi_gun_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value > i:
                        borc_tahsil_suresi_gun_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        borc_tahsil_suresi_gun_colors.append("#00FF00") #Yeşil
                        temp_value = i
                    else:
                        borc_tahsil_suresi_gun_colors.append("#FF0000") #Kırmızı
                        temp_value = i

            result = {'values':borc_tahsil_suresi_gun_values, 'labels':borc_tahsil_suresi_gun_labels, 'colors':borc_tahsil_suresi_gun_colors}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_burut_kar_marji_ceyrek_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "brut_kar_marji_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            brut_kar_marji_ceyrek_values = []
            brut_kar_marji_ceyrek_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    brut_kar_marji_ceyrek_values.append(item.brut_kar_marji_ceyrek)
                    brut_kar_marji_ceyrek_labels.append(item.tarih)

            brut_kar_marji_ceyrek_labels = brut_kar_marji_ceyrek_labels[::-1]
            brut_kar_marji_ceyrek_values = brut_kar_marji_ceyrek_values[::-1]

            result = {'values':brut_kar_marji_ceyrek_values, 'labels':brut_kar_marji_ceyrek_labels}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_burut_kar_marji_yilliklandirilmis_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "brut_kar_marji_yilliklendirilmis_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            brut_kar_marji_yilliklandirilmis_values = []
            brut_kar_marji_yilliklandirilmis_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    brut_kar_marji_yilliklandirilmis_values.append(item.brut_kar_marji_yilliklandirilmis)
                    brut_kar_marji_yilliklandirilmis_labels.append(item.tarih)

            brut_kar_marji_yilliklandirilmis_labels = brut_kar_marji_yilliklandirilmis_labels[::-1]
            brut_kar_marji_yilliklandirilmis_values = brut_kar_marji_yilliklandirilmis_values[::-1]

            result = {'values':brut_kar_marji_yilliklandirilmis_values, 'labels':brut_kar_marji_yilliklandirilmis_labels}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_net_kar_marji_ceyrek_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "net_kar_marji_ceyrek_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    chart_values.append(item.net_kar_marji_ceyreklik)
                    chart_labels.append(item.tarih)

            chart_values = chart_values[::-1]
            chart_labels = chart_labels[::-1]

            result = {'values':chart_values, 'labels':chart_labels}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_net_kar_marji_yillik_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "net_kar_marji_yillik_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    chart_values.append(item.net_kar_marji_yilliklandirilmis)
                    chart_labels.append(item.tarih)

            chart_values = chart_values[::-1]
            chart_labels = chart_labels[::-1]

            result = {'values':chart_values, 'labels':chart_labels}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_faaliyet_giderleri_marji_ceyrek_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "faaliyet_giderleri_marji_ceyrek_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    chart_values.append(item.faaliyet_giderleri_marji_ceyrek)
                    chart_labels.append(item.tarih)

            chart_values = chart_values[::-1]
            chart_labels = chart_labels[::-1]

            result = {'values':chart_values, 'labels':chart_labels}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})


def get_faaliyet_giderleri_marji_yilliklandirilmis_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "faaliyet_giderleri_marji_yilliklandirilmis_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    chart_values.append(item.faaliyet_giderleri_marji_yilliklandirilmis)
                    chart_labels.append(item.tarih)

            chart_values = chart_values[::-1]
            chart_labels = chart_labels[::-1]

            result = {'values':chart_values, 'labels':chart_labels}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})


def get_favok_marji_ceyrek_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "favok_marji_ceyrek_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        
        if cached_value is None:
            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    chart_values.append(item.favok_marji_ceyrek)
                    chart_labels.append(item.tarih)

            chart_values = chart_values[::-1]
            chart_labels = chart_labels[::-1]

            result = {'values':chart_values, 'labels':chart_labels}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})


def get_favok_marji_yillik_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "favok_marji_yillik_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    chart_values.append(item.favok_marji_yilliklandirilmis)
                    chart_labels.append(item.tarih)

            chart_values = chart_values[::-1]
            chart_labels = chart_labels[::-1]

            result = {'values':chart_values, 'labels':chart_labels}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})


def get_ozsermaye_karliligi_yillik_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "ozsermaye_karliligi_yillik_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    chart_values.append(item.ozsermaye_karliligi_yillik)
                    chart_labels.append(item.tarih)

            chart_values = chart_values[::-1]
            chart_labels = chart_labels[::-1]

            result = {'values':chart_values, 'labels':chart_labels}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})



def get_aktif_karlilik_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "aktif_karlilik_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    chart_values.append(item.aktif_karlilik)
                    chart_labels.append(item.tarih)

            chart_values = chart_values[::-1]
            chart_labels = chart_labels[::-1]

            result = {'values':chart_values, 'labels':chart_labels}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})



def get_net_satisler_ceyrek_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "net_satisler_ceyrek_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.net_satislar_ceyrek_mln_tl, float):
                        chart_values.append(item.net_satislar_ceyrek_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_net_satisler_yillik_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için
        cache_key = "net_satisler_yillik_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.net_satislar_yilliklandirilmis_mln_tl, float):
                        chart_values.append(item.net_satislar_yilliklandirilmis_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_net_kar_ceyrek_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "net_kar_ceyrek_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.net_kar_ceyrek_mln_tl, float):
                        chart_values.append(item.net_kar_ceyrek_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})


def get_net_kar_yillik_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "net_kar_yillik_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.net_kar_yilliklandirilmis_mln_tl, float):
                        chart_values.append(item.net_kar_yilliklandirilmis_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})


def get_favok_ceyrek_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "favok_ceyrek_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:

            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.favok_ceyrek_mln_tl, float):
                        chart_values.append(item.favok_ceyrek_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})



def get_favok_yillik_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "favok_yillik_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:

            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.favok_yilliklandirilmis_mln_tl, float):
                        chart_values.append(item.favok_yilliklandirilmis_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_net_finansal_gelirler_giderler_ceyrek_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "net_finansal_gelirler_giderler_ceyrek_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:

            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.net_finansal_gelirler_giderler_ceyrek_mln_tl, float):
                        chart_values.append(item.net_finansal_gelirler_giderler_ceyrek_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})


def get_net_finansal_gelirler_giderler_yillik_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "net_finansal_gelirler_giderler_yillik_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:

            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.net_finansal_gelirler_giderler_yilliklandirilmis_mln_tl, float):
                        chart_values.append(item.net_finansal_gelirler_giderler_yilliklandirilmis_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})


def get_toplam_borclar_mln_tl_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "toplam_borclar_mln_tl_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:

            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.toplam_borclar_mln_tl, float):
                        chart_values.append(item.toplam_borclar_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value > i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)

            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})


def get_net_borc_mln_tl_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "net_borc_mln_tl_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:

            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.net_borc_mln_tl, float):
                        chart_values.append(item.net_borc_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value > i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_net_isletme_sermayesi_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "net_isletme_sermayesi_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:

            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.net_isletme_sermayesi_mln_tl, float):
                        chart_values.append(item.net_isletme_sermayesi_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_ozkaynaklar_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "ozkaynaklar_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:

            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.ozkaynaklar_mln_tl, float):
                        chart_values.append(item.ozkaynaklar_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})


def get_toplam_aktifler_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "toplam_aktifler_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:

            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.toplam_aktifler_mln_tl, float):
                        chart_values.append(item.toplam_aktifler_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})


def get_ozsermaye_karliligi_yillik_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "ozsermaye_karliligi_yillik_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:

            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.ozsermaye_karliligi_yillik, float):
                        chart_values.append(item.ozsermaye_karliligi_yillik)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})
    


#Bank charts
def get_krediler_mevduat_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "krediler_mevduat_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)

        if cached_value is None:

            values = Carpanlar.objects.filter(code=code).order_by('-tarih').all()[:size]
            krediler_mevduat_values = []
            krediler_mevduat_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    krediler_mevduat_values.append(item.krediler_mevduat)
                    krediler_mevduat_labels.append(item.tarih)

            krediler_mevduat_labels = krediler_mevduat_labels[::-1]
            krediler_mevduat_values = krediler_mevduat_values[::-1]


            krediler_mevduat_colors = []
            temp_value = 0
            counter = 0
            for i in krediler_mevduat_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    krediler_mevduat_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value > i:
                        krediler_mevduat_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        krediler_mevduat_colors.append("#00FF00") #Yeşil
                        temp_value = i
                    else:
                        krediler_mevduat_colors.append("#FF0000") #Kırmızı
                        temp_value = i

            result = {'values':krediler_mevduat_values, 'labels':krediler_mevduat_labels, 'colors':krediler_mevduat_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_sermaye_yeterlilik_orani_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "sermaye_yeterlilik_orani_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            sermaye_yeterlilik_orani_values = []
            sermaye_yeterlilik_orani_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    sermaye_yeterlilik_orani_values.append(item.sermaye_yeterlilik_orani)
                    sermaye_yeterlilik_orani_labels.append(item.tarih)

            sermaye_yeterlilik_orani_labels = sermaye_yeterlilik_orani_labels[::-1]
            sermaye_yeterlilik_orani_values = sermaye_yeterlilik_orani_values[::-1]

            result = {'values':sermaye_yeterlilik_orani_values, 'labels':sermaye_yeterlilik_orani_labels}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})
    

def get_net_kar_buyume_ceyrek_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "net_kar_buyume_ceyrek_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            net_kar_buyume_ceyrek_values = []
            net_kar_buyume_ceyrek_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    net_kar_buyume_ceyrek_values.append(item.net_kar_buyume_ceyrek)
                    net_kar_buyume_ceyrek_labels.append(item.tarih)

            net_kar_buyume_ceyrek_labels = net_kar_buyume_ceyrek_labels[::-1]
            net_kar_buyume_ceyrek_values = net_kar_buyume_ceyrek_values[::-1]

            result = {'values':net_kar_buyume_ceyrek_values, 'labels':net_kar_buyume_ceyrek_labels}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})
    
    
def get_ozsermaye_karliligi_yillik_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))  
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "ozsermaye_karliligi_yillik_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Karlilik.objects.filter(code=code).order_by('-tarih').all()[:size]
            ozsermaye_karliligi_yillik_values = []
            ozsermaye_karliligi_yillik_labels = []

            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    ozsermaye_karliligi_yillik_values.append(item.ozsermaye_karliligi_yillik)
                    ozsermaye_karliligi_yillik_labels.append(item.tarih)

            ozsermaye_karliligi_yillik_labels = ozsermaye_karliligi_yillik_labels[::-1]
            ozsermaye_karliligi_yillik_values = ozsermaye_karliligi_yillik_values[::-1]

            result = {'values':ozsermaye_karliligi_yillik_values, 'labels':ozsermaye_karliligi_yillik_labels}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})
    
def get_yilliklandirilmis_net_kar_mln_tl_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "yilliklandirilmis_net_kar_mln_tl_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.yilliklandirilmis_net_kar_mln_tl, float):
                        chart_values.append(item.yilliklandirilmis_net_kar_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})

def get_yilliklanmis_faiz_gelirleri_mln_tl_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "yilliklanmis_faiz_gelirleri_mln_tl_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.yilliklanmis_faiz_gelirleri_mln_tl, float):
                        chart_values.append(item.yilliklanmis_faiz_gelirleri_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})
    
def get_mevduat_buyume_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "mevduat_buyume_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.mevduat_buyume, float):
                        chart_values.append(item.mevduat_buyume)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})
    
    
def get_mevduatlar_mln_tl_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "mevduatlar_mln_tl_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.mevduatlar_mln_tl, float):
                        chart_values.append(item.mevduatlar_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})
    
def get_net_faiz_geliri_ceyrek_mln_tl_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "net_faiz_geliri_ceyrek_mln_tl_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.net_faiz_geliri_ceyrek_mln_tl, float):
                        chart_values.append(item.net_faiz_geliri_ceyrek_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})
    
def get_net_ucret_ve_komisyon_gelirleri_ceyrek_mln_tl_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "net_ucret_ve_komisyon_gelirleri_ceyrek_mln_tl_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.net_ucret_ve_komisyon_gelirleri_ceyrek_mln_tl, float):
                        chart_values.append(item.net_ucret_ve_komisyon_gelirleri_ceyrek_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})
    

def get_ozsermaye_mln_tl_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "ozsermaye_mln_tl_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.ozsermaye_mln_tl, float):
                        chart_values.append(item.ozsermaye_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})


def get_takipteki_krediler_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "takipteki_krediler_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.takipteki_krediler, float):
                        chart_values.append(item.takipteki_krediler)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#FF0000") # Kırmızı
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#00FF00") #Yeşil
                        temp_value = i
                    else:
                        chart_colors.append("#00FF00") #Yeşil
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})
    
def get_krediler_buyume_ceyrek_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "krediler_buyume_ceyrek_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.krediler_buyume, float):
                        chart_values.append(item.krediler_buyume)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})
    

def get_krediler_mln_tl_chart(request):
    if request.GET.get('code') is not None:
        code = request.GET.get('code')
        code = code.upper()

        if request.GET.get('quarterSize') is not None:
            size = int(request.GET.get('quarterSize'))
        else:
            size = 5
        size = size + 2 #çeyrek değerlendirmelerini atlamak için

        cache_key = "krediler_mln_tl_chart_size_" + str(size) + "_" + code
        cache_time = 86400
        cached_value = cache.get(cache_key)
        if cached_value is None:
            values = Finansallar.objects.filter(code=code).order_by('-tarih').all()[:size]
            chart_values = []
            chart_labels = []
            for item in values:
                if 'Çeyrek' in item.tarih or 'Yıllık' in item.tarih:
                    continue
                else:
                    if isinstance(item.krediler_mln_tl, float):
                        chart_values.append(item.krediler_mln_tl)
                    else:
                        chart_values.append(0)
                    chart_labels.append(item.tarih)
            chart_labels = chart_labels[::-1]
            chart_values = chart_values[::-1]

            chart_colors = []
            temp_value = 0
            counter = 0
            for i in chart_values:
                if i == "-":
                    i = 0
                if counter == 0:
                    counter += 1
                    temp_value = i
                    chart_colors.append("#00FF00") #Yeşil renk
                else:
                    if temp_value < i:
                        chart_colors.append("#00FF00") # Yeşil
                        temp_value = i
                    elif temp_value == i:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
                    else:
                        chart_colors.append("#FF0000") #Kırmızı
                        temp_value = i
            result = {'values':chart_values, 'labels':chart_labels, 'colors':chart_colors}
            cache.set(cache_key, result, cache_time)
            return JsonResponse(result)
        else:
            return JsonResponse(cached_value)
    else:
        return JsonResponse({'values':0, 'labels':0})