[![N|Solid](https://www.maib.md/images/logo.svg)](https://www.maib.md)

# Maib Payment Gateway pentru Prestashop v. 8.x
Acest modul vă permite să integrați magazinul dvs. online cu noul **API e-commerce** de la **Modulul Maib Payment Gateway** pentru a accepta plăți online (Visa / Mastercard / Google Pay / Apple Pay).

## Descriere
Pentru a avea posibilitatea de a folosi acest plugin trebuie să fiți înregistrat pe platforma [maibmerchants.md](https://maibmerchants.md).

Imediat după înregistrare, veți putea efectua plăți în mediul de test cu datele de acces din Proiectul de Test.

Pentru a efectua plăți reale trebuie să efectuați cel puțin o tranzacție reușită în mediul de test și să parcurgeți pașii necesari pentru activarea Proiectului de producție.

### Pași pentru activarea Proiectului de Producție
1. Profil completat pe platforma maibmerchants
2. Profil validat
3. Contract e-commerce

## Funcțional
**Plăți online**: Visa / Mastercard / Apple Pay / Google Pay.

**Trei valute**: MDL / USD / EUR (în dependență de setările Proiectului dvs).

**Returnare plată**: Pentru a returna plata, este necesar să actualizați starea comenzii (vedeți _refund.png_) la starea selectată pentru _Plată returnată_ în setările extensiei **Modulul Maib Payment Gateway** (vedeți _settings.png_). Suma plății va fi returnată pe cardul clientului.

## Cerințe 
- Înregistrare pe platforma maibmerchants.md
- Prestashop v. 8.x
- extensiile _curl_ and _json_ activate

## Installation
1. Descărcați modulul de pe Github sau din repozitoriu Prestashop.
2. În panoul de administrare accesați _Module > Manager Module_.
3. Faceți clic pe butonul _Încărcați un modul_ și selectați fișierul de extensie. Odată ce încărcarea este finalizată, Prestashop va începe procesul de instalare.
4. În secțiunea _Plată_, veți vedea un nou modul adăugat **Modulul Maib Payment Gateway**.
5. Faceți clic pe butonul _Instalare_.
6. Faceți clic pe butonul _Configurare_ pentru setările extensiei.

## Setări
1. Project ID - Project ID din maibmerchants.md
2. Project Secret - Project Secret din maibmerchants.md. Este disponibil după activarea proiectului.
3. Signature Key - Signature Key pentru validarea notificărilor pe Callback URL. Este disponibil după activarea proiectului.
4. Ok URL / Fail URL / Callback URL - adăugați aceste link-uri în câmpurile respective ale setărilor Proiectului în maibmerchants.
5. Plată în așteptare - Starea comenzii când plata este în așteptare.
6. Plată cu succes - Starea comenzii când plata este finalizată cu succes.
7. Plată eșuată - Starea comenzii când plata a eșuat.
8. Platã returnatã - Starea comenzii când plata este returnată. Pentru returnarea plății, actualizați starea comenzii la starea selectată aici (vedeți _refund.png_).

## Depanare
Dacă aveți nevoie de asistență suplimentară, vă rugăm să nu ezitați să contactați echipa de asistență ecommerce **Modulul Maib Payment Gateway**, expediind un e-mail la ecom@maib.md.

În e-mailul dvs., asigurați-vă că includeți următoarele informații:
- Numele comerciantului
- Project ID
- Data și ora tranzacției cu erori
- Erori din fișierul cu log-uri