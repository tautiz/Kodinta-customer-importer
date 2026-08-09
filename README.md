# Klientų CSV importas

Paprasta Laravel aplikacija klientų importavimui iš CSV failo.

Prieš importuojant galima peržiūrėti visas CSV eilutes ir matyti rastas klaidas. Į duomenų bazę importuojamos tik validžios eilutės, o netinkamos yra praleidžiamos.

## Paleidimas

Paleidimui reikia Docker Desktop.

Projekto aplanke:

```bash
docker compose up
```

Aplikacija bus pasiekiama per:

`http://localhost:8000`

## CSV pavyzdys

Testinį CSV failą galima rasti čia:

`public/sample-customers.csv`

Faile sudėjau testinius klientus ir kelis specialiai blogus įrašus, kad būtų galima iškart patikrinti validaciją. Prie tokių eilučių pridėta `NEVALIDU`, kad per peržiūrą būtų lengviau jas pastebėti.

Naudojami CSV stulpeliai:

`first_name,last_name,email,phone_number,birth_date,country`

Telefono numeris nėra privalomas. Gimimo data taip pat gali būti tuščia, tačiau jei ji nurodyta, negali būti ateityje.

## Funkcionalumas

* CSV failo peržiūra prieš importuojant;
* validacijos klaidos rodomos prie konkrečios eilutės;
* netinkamos eilutės importo metu praleidžiamos;
* po importo matosi, kiek eilučių importuota ir kiek atmesta;
* saugoma kelių paskutinių importų istorija.

Livewire nenaudojau, nes su juo turiu mazai praktikos ir del riboto laiko nenorejau jo deti i bandomaji darba. Vietoje jo pasirinkau paprastesni Laravel sprendima su standartiniais requestais.

## Ribojimai

Vienas CSV failas gali būti iki 5 MB ir turėti iki 1000 eilučių.

Jei reikėtų importuoti gerokai didesnius failus, importą jau daryčiau per queue, kad visas procesas nevyktų vieno requesto metu.

Šiuo metu šalis saugoma tiesiog kaip tekstas. Atskiro šalių kodų sąrašo ar papildomo mappingo nedariau.
