import asyncio
import aiomysql
from tkinter import Tk, Label, Entry, Scrollbar, VERTICAL, HORIZONTAL, END, messagebox
from tkinter.ttk import Treeview, Style, Button as TButton
import pyperclip
import re

# Veritabanı bağlantısı için asenkron fonksiyon
async def get_db_connection():
    try:
        return await aiomysql.connect(
            host='localhost',
            user='root',
            password='',
            db='burakdatalar'
        )
    except Exception as e:
        messagebox.showerror("Veritabanı Bağlantı Hatası", f"Veritabanı bağlantısı kurulamadı: {e}")
        return None

# Diğer veritabanlarına bağlantı
async def get_other_db_connection(db_name):
    try:
        return await aiomysql.connect(
            host='localhost',
            user='root',
            password='',
            db=db_name
        )
    except Exception as e:
        messagebox.showerror(f"{db_name} Bağlantı Hatası", f"{db_name} veritabanı bağlantısı kurulamadı: {e}")
        return None

# Asenkron sorgu fonksiyonu
async def fetch_data(gsm=None, tc=None): 
    results = []
    conn = await get_db_connection()
    if not conn:
        return results  # Bağlantı sağlanamazsa boş liste döndür

    async with conn.cursor(aiomysql.DictCursor) as cursor:
        try:
            if gsm:
                1
                # GSM ile TC sorgula
                await cursor.execute("SELECT TC FROM gsm WHERE GSM = %s", (gsm,))
                tc_rows = await cursor.fetchall()
                if not tc_rows:
                    messagebox.showinfo("Bilgi", "GSM numarasına ait kişi bulunamadı!")
                else:
                    tc = tc_rows[0]['TC']  # GSM ile ilişkilendirilmiş TC'yi al

                    # TC ile ilişkili GSM numaralarını getirme
                    await cursor.execute("SELECT GSM FROM gsm WHERE TC = %s", (tc,))
                    gsm_numbers = await cursor.fetchall()
                    gsm_list = ", ".join([gsm_number['GSM'] for gsm_number in gsm_numbers]) if gsm_numbers else "Bulunamadı!"
                    
                    # 101M tablosundan TC ile ilişkili tüm kayıtları getir
                    await cursor.execute("SELECT * FROM 101m WHERE TC = %s", (tc,))
                    records = await cursor.fetchall()

                    if not records:
                        messagebox.showinfo("Bilgi", "TC numarasına ait başka bir kayıt bulunamadı!")
                    
                    for record in records:
                        # Adres bilgisini al
                        address = await fetch_address(tc)
                        
                        results.append({
                            'YAKINLIK': 'KENDİSİ',
                            'TC': record['TC'] or "Bulunamadı!",
                            'ADI': record['ADI'] or "Bulunamadı!",
                            'SOYADI': record['SOYADI'] or "Bulunamadı!",
                            'DOGUMTARIHI': record['DOGUMTARIHI'] or "Bulunamadı!",
                            'ANNEADI': record['ANNEADI'] or "Bulunamadı!",
                            'ANNETC': record['ANNETC'] or "Bulunamadı!",
                            'BABAADI': record['BABAADI'] or "Bulunamadı!",
                            'BABATC': record['BABATC'] or "Bulunamadı!",
                            'NUFUSIL': record['NUFUSIL'] or "Bulunamadı!",
                            'NUFUSILCE': record['NUFUSILCE'] or "Bulunamadı!",
                            'UYRUK': record['UYRUK'] or "Bulunamadı!",
                            'GSM': gsm_list,
                            'ADRES': address or "Bulunamadı!"  # Adres sütunu
                        })

                        # Kardeş sorgusu
                        await cursor.execute("SELECT * FROM 101m WHERE (BABATC = %s OR ANNETC = %s) AND TC != %s", (record['BABATC'], record['ANNETC'], record['TC']))
                        kardes_records = await cursor.fetchall()
                        for kardes_record in kardes_records:
                            # Kardeş GSM bilgileri
                            await cursor.execute("SELECT GSM FROM gsm WHERE TC = %s", (kardes_record['TC'],))
                            kardes_gsm_numbers = await cursor.fetchall()
                            kardes_gsm_list = ", ".join([kardes_gsm['GSM'] for kardes_gsm in kardes_gsm_numbers]) if kardes_gsm_numbers else "Bulunamadı!"
                            
                            # Kardeşlerin adres bilgisi
                            address = await fetch_address(kardes_record['TC'])
                            
                            results.append({
                                'YAKINLIK': 'KARDEŞİ',
                                'TC': kardes_record['TC'] or "Bulunamadı!",
                                'ADI': kardes_record['ADI'] or "Bulunamadı!",
                                'SOYADI': kardes_record['SOYADI'] or "Bulunamadı!",
                                'DOGUMTARIHI': kardes_record['DOGUMTARIHI'] or "Bulunamadı!",
                                'ANNEADI': kardes_record['ANNEADI'] or "Bulunamadı!",
                                'ANNETC': kardes_record['ANNETC'] or "Bulunamadı!",
                                'BABAADI': kardes_record['BABAADI'] or "Bulunamadı!",
                                'BABATC': kardes_record['BABATC'] or "Bulunamadı!",
                                'NUFUSIL': kardes_record['NUFUSIL'] or "Bulunamadı!",
                                'NUFUSILCE': kardes_record['NUFUSILCE'] or "Bulunamadı!",
                                'UYRUK': kardes_record['UYRUK'] or "Bulunamadı!",
                                'GSM': kardes_gsm_list,
                                'ADRES': address or "Bulunamadı!"  # Kardeşin adresi
                            })
                   # Çocuk Sorgusu
                await cursor.execute("SELECT * FROM 101m WHERE (BABATC = %s OR ANNETC = %s) AND TC != %s", (record['TC'], record['TC'], record['TC']))
                cocugu_records = await cursor.fetchall()
                for cocugu_record in cocugu_records:
                    # Çocuk GSM bilgileri
                    await cursor.execute("SELECT GSM FROM gsm WHERE TC = %s", (cocugu_record['TC'],))
                    cocugu_gsm_numbers = await cursor.fetchall()
                    cocugu_gsm_list = ", ".join([cocugu_gsm['GSM'] for cocugu_gsm in cocugu_gsm_numbers]) if cocugu_gsm_numbers else "Bulunamadı!"
                    
                    # Çocuğun adres bilgisi
                    cocugu_address = await fetch_address(cocugu_record['TC'])

                    results.append({
                        'YAKINLIK': 'ÇOCUĞU',
                        'TC': cocugu_record['TC'] or "Bulunamadı!",
                        'ADI': cocugu_record['ADI'] or "Bulunamadı!",
                        'SOYADI': cocugu_record['SOYADI'] or "Bulunamadı!",
                        'DOGUMTARIHI': cocugu_record['DOGUMTARIHI'] or "Bulunamadı!",
                        'ANNEADI': cocugu_record['ANNEADI'] or "Bulunamadı!",
                        'ANNETC': cocugu_record['ANNETC'] or "Bulunamadı!",
                        'BABAADI': cocugu_record['BABAADI'] or "Bulunamadı!",
                        'BABATC': cocugu_record['BABATC'] or "Bulunamadı!",
                        'NUFUSIL': cocugu_record['NUFUSIL'] or "Bulunamadı!",
                        'NUFUSILCE': cocugu_record['NUFUSILCE'] or "Bulunamadı!",
                        'UYRUK': cocugu_record['UYRUK'] or "Bulunamadı!",
                        'GSM': cocugu_gsm_list,
                        'ADRES': cocugu_address or "Bulunamadı!"  # Çocuğun adresi
                    })

                    # Torun Sorgusu
                    await cursor.execute("SELECT * FROM 101m WHERE (BABATC = %s OR ANNETC = %s) AND TC != %s", (cocugu_record['TC'], cocugu_record['TC'], cocugu_record['TC']))
                    torun_records = await cursor.fetchall()
                    for torun_record in torun_records:
                        # Torun GSM bilgileri
                        await cursor.execute("SELECT GSM FROM gsm WHERE TC = %s", (torun_record['TC'],))
                        torun_gsm_numbers = await cursor.fetchall()
                        torun_gsm_list = ", ".join([torun_gsm['GSM'] for torun_gsm in torun_gsm_numbers]) if torun_gsm_numbers else "Bulunamadı!"
                        
                        # Torunun adres bilgisi
                        torun_address = await fetch_address(torun_record['TC'])

                        results.append({
                            'YAKINLIK': 'TORUNU',
                            'TC': torun_record['TC'] or "Bulunamadı!",
                            'ADI': torun_record['ADI'] or "Bulunamadı!",
                            'SOYADI': torun_record['SOYADI'] or "Bulunamadı!",
                            'DOGUMTARIHI': torun_record['DOGUMTARIHI'] or "Bulunamadı!",
                            'ANNEADI': torun_record['ANNEADI'] or "Bulunamadı!",
                            'ANNETC': torun_record['ANNETC'] or "Bulunamadı!",
                            'BABAADI': torun_record['BABAADI'] or "Bulunamadı!",
                            'BABATC': torun_record['BABATC'] or "Bulunamadı!",
                            'NUFUSIL': torun_record['NUFUSIL'] or "Bulunamadı!",
                            'NUFUSILCE': torun_record['NUFUSILCE'] or "Bulunamadı!",
                            'UYRUK': torun_record['UYRUK'] or "Bulunamadı!",
                            'GSM': torun_gsm_list,
                            'ADRES': torun_address or "Bulunamadı!"  # Torunun adresi
                        })

                # Baba sorgusu
                await cursor.execute("SELECT * FROM 101m WHERE TC = %s", (record['BABATC'],))
                baba_records = await cursor.fetchall()
                for baba_record in baba_records:
                    # Baba GSM bilgileri
                    await cursor.execute("SELECT GSM FROM gsm WHERE TC = %s", (baba_record['TC'],))
                    baba_gsm_numbers = await cursor.fetchall()
                    baba_gsm_list = ", ".join([baba_gsm['GSM'] for baba_gsm in baba_gsm_numbers]) if baba_gsm_numbers else "Bulunamadı!"
                    
                    # Babanın adres bilgisi
                    baba_address = await fetch_address(baba_record['TC'])

                    results.append({
                        'YAKINLIK': 'BABASI',
                        'TC': baba_record['TC'] or "Bulunamadı!",
                        'ADI': baba_record['ADI'] or "Bulunamadı!",
                        'SOYADI': baba_record['SOYADI'] or "Bulunamadı!",
                        'DOGUMTARIHI': baba_record['DOGUMTARIHI'] or "Bulunamadı!",
                        'ANNEADI': baba_record['ANNEADI'] or "Bulunamadı!",
                        'ANNETC': baba_record['ANNETC'] or "Bulunamadı!",
                        'BABAADI': baba_record['BABAADI'] or "Bulunamadı!",
                        'BABATC': baba_record['BABATC'] or "Bulunamadı!",
                        'NUFUSIL': baba_record['NUFUSIL'] or "Bulunamadı!",
                        'NUFUSILCE': baba_record['NUFUSILCE'] or "Bulunamadı!",
                        'UYRUK': baba_record['UYRUK'] or "Bulunamadı!",
                        'GSM': baba_gsm_list,
                        'ADRES': baba_address or "Bulunamadı!"  # Babanın adresi
                    })

                # Anne sorgusu
                await cursor.execute("SELECT * FROM 101m WHERE TC = %s", (record['ANNETC'],))
                anne_records = await cursor.fetchall()
                for anne_record in anne_records:
                    # Anne GSM bilgileri
                    await cursor.execute("SELECT GSM FROM gsm WHERE TC = %s", (anne_record['TC'],))
                    anne_gsm_numbers = await cursor.fetchall()
                    anne_gsm_list = ", ".join([anne_gsm['GSM'] for anne_gsm in anne_gsm_numbers]) if anne_gsm_numbers else "Bulunamadı!"
                    
                    # Annenin adres bilgisi
                    anne_address = await fetch_address(anne_record['TC'])

                    results.append({
                        'YAKINLIK': 'ANNESİ',
                        'TC': anne_record['TC'] or "Bulunamadı!",
                        'ADI': anne_record['ADI'] or "Bulunamadı!",
                        'SOYADI': anne_record['SOYADI'] or "Bulunamadı!",
                        'DOGUMTARIHI': anne_record['DOGUMTARIHI'] or "Bulunamadı!",
                        'ANNEADI': anne_record['ANNEADI'] or "Bulunamadı!",
                        'ANNETC': anne_record['ANNETC'] or "Bulunamadı!",
                        'BABAADI': anne_record['BABAADI'] or "Bulunamadı!",
                        'BABATC': anne_record['BABATC'] or "Bulunamadı!",
                        'NUFUSIL': anne_record['NUFUSIL'] or "Bulunamadı!",
                        'NUFUSILCE': anne_record['NUFUSILCE'] or "Bulunamadı!",
                        'UYRUK': anne_record['UYRUK'] or "Bulunamadı!",
                        'GSM': anne_gsm_list,
                        'ADRES': anne_address or "Bulunamadı!"  # Annenin adresi
                    })
                                                     

            elif tc:
                # TC ile sorgu
                await cursor.execute("SELECT * FROM 101m WHERE TC = %s", (tc,))
                tc_rows = await cursor.fetchall()
                if not tc_rows:
                    messagebox.showinfo("Bilgi", "TC numarasına ait kişi bulunamadı!")

                # TC ile ilişkili GSM numaralarını getirme
                if tc_rows:
                    await cursor.execute("SELECT GSM FROM gsm WHERE TC = %s", (tc,))
                    gsm_numbers = await cursor.fetchall()
                    gsm_list = ", ".join([gsm_number['GSM'] for gsm_number in gsm_numbers]) if gsm_numbers else "Bulunamadı!"
                    for tc_row in tc_rows:
                        tc = tc_row['TC']
                        # 101M tablosundan TC ile ilişkili tüm kayıtları getir
                        await cursor.execute("SELECT * FROM 101m WHERE TC = %s", (tc,))
                        records = await cursor.fetchall()

                        if not records:
                            messagebox.showinfo("Bilgi", "TC numarasına ait başka bir kayıt bulunamadı!")
                        
                        for record in records:
                            # Adres bilgisini al
                            address = await fetch_address(tc)
                            
                            results.append({
                                'YAKINLIK': 'KENDİSİ',
                                'TC': record['TC'] or "Bulunamadı!",
                                'ADI': record['ADI'] or "Bulunamadı!",
                                'SOYADI': record['SOYADI'] or "Bulunamadı!",
                                'DOGUMTARIHI': record['DOGUMTARIHI'] or "Bulunamadı!",
                                'ANNEADI': record['ANNEADI'] or "Bulunamadı!",
                                'ANNETC': record['ANNETC'] or "Bulunamadı!",
                                'BABAADI': record['BABAADI'] or "Bulunamadı!",
                                'BABATC': record['BABATC'] or "Bulunamadı!",
                                'NUFUSIL': record['NUFUSIL'] or "Bulunamadı!",
                                'NUFUSILCE': record['NUFUSILCE'] or "Bulunamadı!",
                                'UYRUK': record['UYRUK'] or "Bulunamadı!",
                                'GSM': gsm_list,
                                'ADRES': address or "Bulunamadı!"  # Adres sütunu
                            })


                        # Kardeş sorgusu
                        await cursor.execute("SELECT * FROM 101m WHERE (BABATC = %s OR ANNETC = %s) AND TC != %s", (record['BABATC'], record['ANNETC'], record['TC']))
                        kardes_records = await cursor.fetchall()
                        for kardes_record in kardes_records:
                            # Kardeş GSM bilgileri
                            await cursor.execute("SELECT GSM FROM gsm WHERE TC = %s", (kardes_record['TC'],))
                            kardes_gsm_numbers = await cursor.fetchall()
                            kardes_gsm_list = ", ".join([kardes_gsm['GSM'] for kardes_gsm in kardes_gsm_numbers]) if kardes_gsm_numbers else "Bulunamadı!"
                            
                            # Kardeşlerin adres bilgisi
                            address = await fetch_address(kardes_record['TC'])
                            
                            results.append({
                                'YAKINLIK': 'KARDEŞİ',
                                'TC': kardes_record['TC'] or "Bulunamadı!",
                                'ADI': kardes_record['ADI'] or "Bulunamadı!",
                                'SOYADI': kardes_record['SOYADI'] or "Bulunamadı!",
                                'DOGUMTARIHI': kardes_record['DOGUMTARIHI'] or "Bulunamadı!",
                                'ANNEADI': kardes_record['ANNEADI'] or "Bulunamadı!",
                                'ANNETC': kardes_record['ANNETC'] or "Bulunamadı!",
                                'BABAADI': kardes_record['BABAADI'] or "Bulunamadı!",
                                'BABATC': kardes_record['BABATC'] or "Bulunamadı!",
                                'NUFUSIL': kardes_record['NUFUSIL'] or "Bulunamadı!",
                                'NUFUSILCE': kardes_record['NUFUSILCE'] or "Bulunamadı!",
                                'UYRUK': kardes_record['UYRUK'] or "Bulunamadı!",
                                'GSM': kardes_gsm_list,
                                'ADRES': address or "Bulunamadı!"  # Kardeşin adresi
                            })
                   # Çocuk Sorgusu
                await cursor.execute("SELECT * FROM 101m WHERE (BABATC = %s OR ANNETC = %s) AND TC != %s", (record['TC'], record['TC'], record['TC']))
                cocugu_records = await cursor.fetchall()
                for cocugu_record in cocugu_records:
                    # Çocuk GSM bilgileri
                    await cursor.execute("SELECT GSM FROM gsm WHERE TC = %s", (cocugu_record['TC'],))
                    cocugu_gsm_numbers = await cursor.fetchall()
                    cocugu_gsm_list = ", ".join([cocugu_gsm['GSM'] for cocugu_gsm in cocugu_gsm_numbers]) if cocugu_gsm_numbers else "Bulunamadı!"
                    
                    # Çocuğun adres bilgisi
                    cocugu_address = await fetch_address(cocugu_record['TC'])

                    results.append({
                        'YAKINLIK': 'ÇOCUĞU',
                        'TC': cocugu_record['TC'] or "Bulunamadı!",
                        'ADI': cocugu_record['ADI'] or "Bulunamadı!",
                        'SOYADI': cocugu_record['SOYADI'] or "Bulunamadı!",
                        'DOGUMTARIHI': cocugu_record['DOGUMTARIHI'] or "Bulunamadı!",
                        'ANNEADI': cocugu_record['ANNEADI'] or "Bulunamadı!",
                        'ANNETC': cocugu_record['ANNETC'] or "Bulunamadı!",
                        'BABAADI': cocugu_record['BABAADI'] or "Bulunamadı!",
                        'BABATC': cocugu_record['BABATC'] or "Bulunamadı!",
                        'NUFUSIL': cocugu_record['NUFUSIL'] or "Bulunamadı!",
                        'NUFUSILCE': cocugu_record['NUFUSILCE'] or "Bulunamadı!",
                        'UYRUK': cocugu_record['UYRUK'] or "Bulunamadı!",
                        'GSM': cocugu_gsm_list,
                        'ADRES': cocugu_address or "Bulunamadı!"  # Çocuğun adresi
                    })

                    # Torun Sorgusu
                    await cursor.execute("SELECT * FROM 101m WHERE (BABATC = %s OR ANNETC = %s) AND TC != %s", (cocugu_record['TC'], cocugu_record['TC'], cocugu_record['TC']))
                    torun_records = await cursor.fetchall()
                    for torun_record in torun_records:
                        # Torun GSM bilgileri
                        await cursor.execute("SELECT GSM FROM gsm WHERE TC = %s", (torun_record['TC'],))
                        torun_gsm_numbers = await cursor.fetchall()
                        torun_gsm_list = ", ".join([torun_gsm['GSM'] for torun_gsm in torun_gsm_numbers]) if torun_gsm_numbers else "Bulunamadı!"
                        
                        # Torunun adres bilgisi
                        torun_address = await fetch_address(torun_record['TC'])

                        results.append({
                            'YAKINLIK': 'TORUNU',
                            'TC': torun_record['TC'] or "Bulunamadı!",
                            'ADI': torun_record['ADI'] or "Bulunamadı!",
                            'SOYADI': torun_record['SOYADI'] or "Bulunamadı!",
                            'DOGUMTARIHI': torun_record['DOGUMTARIHI'] or "Bulunamadı!",
                            'ANNEADI': torun_record['ANNEADI'] or "Bulunamadı!",
                            'ANNETC': torun_record['ANNETC'] or "Bulunamadı!",
                            'BABAADI': torun_record['BABAADI'] or "Bulunamadı!",
                            'BABATC': torun_record['BABATC'] or "Bulunamadı!",
                            'NUFUSIL': torun_record['NUFUSIL'] or "Bulunamadı!",
                            'NUFUSILCE': torun_record['NUFUSILCE'] or "Bulunamadı!",
                            'UYRUK': torun_record['UYRUK'] or "Bulunamadı!",
                            'GSM': torun_gsm_list,
                            'ADRES': torun_address or "Bulunamadı!"  # Torunun adresi
                        })

                # Baba sorgusu
                await cursor.execute("SELECT * FROM 101m WHERE TC = %s", (record['BABATC'],))
                baba_records = await cursor.fetchall()
                for baba_record in baba_records:
                    # Baba GSM bilgileri
                    await cursor.execute("SELECT GSM FROM gsm WHERE TC = %s", (baba_record['TC'],))
                    baba_gsm_numbers = await cursor.fetchall()
                    baba_gsm_list = ", ".join([baba_gsm['GSM'] for baba_gsm in baba_gsm_numbers]) if baba_gsm_numbers else "Bulunamadı!"
                    
                    # Babanın adres bilgisi
                    baba_address = await fetch_address(baba_record['TC'])

                    results.append({
                        'YAKINLIK': 'BABASI',
                        'TC': baba_record['TC'] or "Bulunamadı!",
                        'ADI': baba_record['ADI'] or "Bulunamadı!",
                        'SOYADI': baba_record['SOYADI'] or "Bulunamadı!",
                        'DOGUMTARIHI': baba_record['DOGUMTARIHI'] or "Bulunamadı!",
                        'ANNEADI': baba_record['ANNEADI'] or "Bulunamadı!",
                        'ANNETC': baba_record['ANNETC'] or "Bulunamadı!",
                        'BABAADI': baba_record['BABAADI'] or "Bulunamadı!",
                        'BABATC': baba_record['BABATC'] or "Bulunamadı!",
                        'NUFUSIL': baba_record['NUFUSIL'] or "Bulunamadı!",
                        'NUFUSILCE': baba_record['NUFUSILCE'] or "Bulunamadı!",
                        'UYRUK': baba_record['UYRUK'] or "Bulunamadı!",
                        'GSM': baba_gsm_list,
                        'ADRES': baba_address or "Bulunamadı!"  # Babanın adresi
                    })

                # Anne sorgusu
                await cursor.execute("SELECT * FROM 101m WHERE TC = %s", (record['ANNETC'],))
                anne_records = await cursor.fetchall()
                for anne_record in anne_records:
                    # Anne GSM bilgileri
                    await cursor.execute("SELECT GSM FROM gsm WHERE TC = %s", (anne_record['TC'],))
                    anne_gsm_numbers = await cursor.fetchall()
                    anne_gsm_list = ", ".join([anne_gsm['GSM'] for anne_gsm in anne_gsm_numbers]) if anne_gsm_numbers else "Bulunamadı!"
                    
                    # Annenin adres bilgisi
                    anne_address = await fetch_address(anne_record['TC'])

                    results.append({
                        'YAKINLIK': 'ANNESİ',
                        'TC': anne_record['TC'] or "Bulunamadı!",
                        'ADI': anne_record['ADI'] or "Bulunamadı!",
                        'SOYADI': anne_record['SOYADI'] or "Bulunamadı!",
                        'DOGUMTARIHI': anne_record['DOGUMTARIHI'] or "Bulunamadı!",
                        'ANNEADI': anne_record['ANNEADI'] or "Bulunamadı!",
                        'ANNETC': anne_record['ANNETC'] or "Bulunamadı!",
                        'BABAADI': anne_record['BABAADI'] or "Bulunamadı!",
                        'BABATC': anne_record['BABATC'] or "Bulunamadı!",
                        'NUFUSIL': anne_record['NUFUSIL'] or "Bulunamadı!",
                        'NUFUSILCE': anne_record['NUFUSILCE'] or "Bulunamadı!",
                        'UYRUK': anne_record['UYRUK'] or "Bulunamadı!",
                        'GSM': anne_gsm_list,
                        'ADRES': anne_address or "Bulunamadı!"  # Annenin adresi
                    })



        except Exception as e:
            messagebox.showerror("Veritabanı Hata", f"Sorgu sırasında bir hata oluştu: {e}")

    conn.close()
    return results

# Adres bilgisini almak için başka veritabanlarına sorgu
async def fetch_address(tc):
    address = None
    for db_name in ['data', 'veri']:  # Her iki veritabanında sorgu yapıyoruz
        conn = await get_other_db_connection(db_name)
        if conn:
            async with conn.cursor(aiomysql.DictCursor) as cursor:
                try:
                    await cursor.execute("SELECT Ikametgah FROM datam WHERE KimlikNo = %s", (tc,))
                    rows = await cursor.fetchall()
                    if rows:
                        address = rows[0]['Ikametgah']
                        break  # Adres bulunduysa döngüyü kırıyoruz
                except Exception as e:
                    messagebox.showerror(f"{db_name} Hata", f"Adres sorgusu sırasında hata oluştu: {e}")
            conn.close()
    return address

# Tkinter GUI kısmı
class App:
    def __init__(self, root):
        self.root = root
        self.root.title("burak.py Sorgulama")
        self.root.geometry("1420x700")
        self.root.configure(bg='#2f2f2f')

        # GSM No girişi
        Label(root, text="GSM No:", bg='#2f2f2f', fg='white', font=('Arial', 12)).grid(row=0, column=0, padx=5, pady=5)  # padx ve pady değeri azaltıldı
        self.gsm_entry = Entry(root, width=30)
        self.gsm_entry.grid(row=0, column=1, padx=10, pady=5, sticky='w')

        # TC No girişi
        Label(root, text="TC No:", bg='#2f2f2f', fg='white', font=('Arial', 12)).grid(row=1, column=0, padx=5, pady=5)  # padx ve pady değeri azaltıldı
        self.tc_entry = Entry(root, width=30)
        self.tc_entry.grid(row=1, column=1, padx=10, pady=5, sticky='w')

        # Stil
        style = Style()
        style.configure('TButton', background='#FF8C00', foreground='white', font=('Arial', 12, 'bold'))  # Koyu turuncu renk

        from tkinter import Button

        Button(root, text="GSM ile Sorgula", command=self.query, width=20, height=2).grid(row=0, column=2, padx=5, pady=5)  # pady değeri azaltıldı
        Button(root, text="TC ile Sorgula", command=self.query_by_tc, width=20, height=2).grid(row=1, column=2, padx=5, pady=5)  # pady değeri azaltıldı

        # Grid genişletme özellikleri
        root.columnconfigure(0, weight=0)
        root.columnconfigure(1, weight=0)
        root.columnconfigure(2, weight=1)
        root.rowconfigure(2, weight=1)  # Tablo satırı genişletildi

        # Sonuç tablosu
        self.tree = Treeview(root, columns=("YAKINLIK", "TC", "ADI", "SOYADI", "DOGUMTARIHI", "ANNEADI", "ANNETC", "BABAADI", "BABATC", "NUFUSIL", "NUFUSILCE", "ADRES", "UYRUK", "GSM"), show="headings", height=20)  # height değeri artırıldı
        self.tree.grid(row=2, column=0, columnspan=3, padx=10, pady=10, sticky='nsew')  # sticky 'nsew' ile alanı genişlettik

        for col in self.tree['columns']:
            self.tree.heading(col, text=col)
            self.tree.column(col, width=100, stretch=True)  # width değeri artırıldı

        # Copyright
        Label(root, text="Copyright: burak.py | Instagram: @burakdevelopr", bg='#2f2f2f', fg='white').grid(row=3, column=0, columnspan=3, padx=10, pady=10, sticky='ew')

    def query(self):
        gsm = self.gsm_entry.get()
        if not gsm or not self.is_valid_gsm(gsm):
            messagebox.showerror("Hata", "Geçersiz GSM numarası!")
            return
        asyncio.run(self.fetch_and_display(gsm=gsm))

    def query_by_tc(self):
        tc = self.tc_entry.get()
        if not tc or not self.is_valid_tc(tc):
            messagebox.showerror("Hata", "Geçersiz TC numarası!")
            return
        asyncio.run(self.fetch_and_display(tc=tc)) # Parametre olarak TC gönderiyoruz

    async def fetch_and_display(self, gsm=None, tc=None):
        results = await fetch_data(gsm=gsm, tc=tc) # Parametreleri fonksiyona iletiyoruz
        for i in self.tree.get_children():
            self.tree.delete(i)

        if not results:
            messagebox.showinfo("Bilgi", "Aradığınız kritere uygun sonuç bulunamadı.")

        for row in results:
            self.tree.insert("", END, values=(

                str(row['YAKINLIK']), str(row['TC']), str(row['ADI']), str(row['SOYADI']), str(row['DOGUMTARIHI']),
                str(row['ANNEADI']), str(row['ANNETC']), str(row['BABAADI']), str(row['BABATC']),
                str(row['NUFUSIL']), str(row['NUFUSILCE']), str(row['ADRES']), str(row['UYRUK']), str(row['GSM'])
            ))

        # Dosya adı belirleme: GSM veya TC'yi kullanarak dosya ismi oluşturuyoruz
        if gsm:
            log_file_name = f"{gsm}_sonuc.txt"  # GSM sorgusuna göre dosya adı
        elif tc:
            log_file_name = f"{tc}_sonuc.txt"  # TC sorgusuna göre dosya adı
        else:
            log_file_name = "sonuc.txt"  # Varsayılan dosya adı

        # Sonuçları dosyaya kaydetme
        with open(log_file_name, "w") as log_file:
            # Başlıkları dosyaya yazıyoruz
            log_file.write(f"burak.py Sorgu Servisi\n\n")
            if gsm:
                log_file.write(f"Sorgulanan GSM: {gsm}\n\n")
            elif tc:
                log_file.write(f"Sorgulanan TC: {tc}\n\n")
            
            for row in results:
                log_file.write(f"YAKINLIK: {row['YAKINLIK']}\n")
                log_file.write(f"TC: {row['TC']}\n")
                log_file.write(f"ADI: {row['ADI']}\n")
                log_file.write(f"SOYADI: {row['SOYADI']}\n")
                log_file.write(f"DOGUMTARIHI: {row['DOGUMTARIHI']}\n")
                log_file.write(f"ANNEADI: {row['ANNEADI']}\n")
                log_file.write(f"ANNETC: {row['ANNETC']}\n")
                log_file.write(f"BABAADI: {row['BABAADI']}\n")
                log_file.write(f"BABATC: {row['BABATC']}\n")
                log_file.write(f"NUFUSIL: {row['NUFUSIL']}\n")
                log_file.write(f"NUFUSILCE: {row['NUFUSILCE']}\n")
                log_file.write(f"ADRES: {row['ADRES']}\n")
                log_file.write(f"UYRUK: {row['UYRUK']}\n")
                log_file.write(f"GSM: {row['GSM']}\n")
                log_file.write("="*40 + "\n")

    # GSM geçerliliği kontrolü
    def is_valid_gsm(self, gsm):
        return bool(re.match(r'^[0-9]{10}$', gsm))

    # TC geçerliliği kontrolü
    def is_valid_tc(self, tc):
        return bool(re.match(r'^[0-9]{11}$', tc))

# Ana pencereyi başlatma
root = Tk()
app = App(root)
root.mainloop()
