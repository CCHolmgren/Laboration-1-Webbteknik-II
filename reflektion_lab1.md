Vad tror Du vi har för skäl till att spara det skrapade datat i JSON-format?
=====================
JSON är ett format som stöds i de flesta programmeringsspråk, det är lätt att arbeta med då det oftast enbart resulterar i arrayer och dictionaries när det decodas igen, det är ett kompakt format som fortfarande är ungefär lika användarvänligt som XML är medan det är mycket mer kompakt, om man bemödar sig att lägga upp det på ett sådant sätt. Vi skulle givetvis kunna använda något annat format, typ XML eller YAML, men många APIer och liknande brukar använda sig av JSON, så det är även där ett bra val.

Olika jämförelsesiter är flitiga användare av webbskrapor. Kan du komma på fler typer av tillämplingar där webbskrapor förekommer?
=====================
Sökmotorer är ett annat väldigt tydligt exempel av webbskrapor. De är oftast mest en spindel som går igenom alla sidor de kan hitta, men det blir mer och mer vanligt att de skrapar data som befinner sig på sidorna, så som v-cards och liknande. Man kan ju givetvis diskutera om det inte är webscraping också, eftersom de går igenom alla sidor och inte bara besöker adresserna utan också sparar ner dessa och annan information om sidan de hamnar på.

Hur har du i din skrapning underlättat för serverägaren?
=====================
Jag har försökt att underlätta i min skrapning genom att lägga in en grundläggande broms, som bromsar i några microsekunder mellan varje skrapning. Jag har också en tidsbegränsning som gör att det inte går att skrapa oftare än var 5:e minut.

Vilka etiska aspekter bör man fundera kring vid webbskrapning?
=====================
Om man skapar en dynamiskt genererad sida så bör man vara försiktig med hur man skrapar sidan för att inte lägga allt för stor belastning på servern. Inte heller bör man skapa sidor som finns nämnda i robots.txt då de oftast antingen är helt onödiga att skrapa, innehåller dynamisk data som ägarna inte vill att man ska skrapa eller liknande. När man skrapa så kopierar man ju också effektivt data som någon annan äger, och då träder copyright och andra liknande lagar in och är jobbiga.

Vad finns det för risker med applikationer som innefattar automatisk skrapning av webbsidor? Nämn minst ett par stycken!
=====================
1. Förutsätter saker om någonannans system, vilket inte är så bra om det visar sig att detta system förändras utan att skrapningen kan hantera detta. 
2. När man skrapar en sida så hanterar man en stor del osäker data, eftersom man får allt från en annan källa än sitt sluta system. Om denna osäkra data råkar vara farlig på något sätt och den samtidigt hanteras på ett felaktigt sätt så kan nästan vad som helst hända på servern. Här gäller ju givetvis att aldrig lita på datan man skrapar, att se till att validera denna och att den sparas på ett säkert sätt.
3. Det kan ta väldigt lång tid att skrapa en sida om det är en långsam server (*host*coursepress*host*), men det kan man antagligen lösa genom att ha ett cronjob som sköter skrapningen och ett annat skrip som sköter presentationen, med en databas som lagringsmedium mellan dessa.

Tänk dig att du skulle skrapa en sida gjord i ASP.NET WebForms. Vad för extra problem skulle man kunna få då?
=====================
Jag kan föreställa mig att det blir problem eftersom XPaths antagligen skulle bli komplicerade eller välja väldigt mycket då WebForms använder sig av någon typ av automatisk generering som inte riktigt är det bästa när det kommer till HTML standard och liknande. Det går i alla fal inte att göra något generellt. Om vi förutsätter att vi ska skicka in värden via forms och så, så är det nog bäst att man använder en webbläsare, Mozilla Firefox text, och programmerar en skrapa därifrån. Det blir mycket invecklat väldigt fort om man ska göra det.


Välj ut två punkter kring din kod du tycker är värd att diskutera vid redovisningen. Det kan röra val du gjort, tekniska lösningar eller lösningar du inte är riktigt nöjd med.
=====================
1. Faktumet att man kan starta ett PHP-script och sedan avsluta det medan scriptet fortfarande kör i bakgrunden är ganska trevlig. Jag valde att göra det på det här sättet för att slippa sköta forms och liknande som annars skulle få lov att köra. 
2. Hela koden. Allt är egentligen väldigt fult, men jag vet inte riktigt hur man skulle kunna göra det bättre än hur jag har gjort det, på det sättet som jag har valt att göra det på. Men eftersom det handlar om att skrapa en sida och det innebär mycket sträng beroenden så kanske det helt enkelt är så. Jag skulle givetvis kunna göra funktionerna mer dynamiska så att det hanteras på ett bättre sätt och att det är enklare att bygga den för vilken sida som helst...


Hitta ett rättsfall som handlar om webbskrapning. Redogör kort för detta.
=====================
De rättsfall som jag har tittat över har mestadels handlat om webscraping när det också handlar om andra saker samtidigt. eBay v. Bidder's Edge handlar om att eBay inte ville tillåta Bidder's Edge att skrapa deras produkter, men då på grund av att de samtidigt lade automatiska bud på sakerna, vilket inte var förenligt med eBay's TOS. För att man ska kunna hävda att skrapningen var otillåten så måste man kunna visa att det har skadat en ekonomiskt och att de gjorde det avsiktligen, något som de gjorde i det här fallet.     
Många fall finns om just detta, att någon har skrapat en sida med avsikten att tjäna pengar eller liknande, men det finns få fall som är intressanta där enbart webscraping har förekommit och det blivit ett rättsfall av det.

Känner du att du lärt dig något av denna uppgift?
=====================
1. PHP är än en gång drygt att jobba i, men det är trevligt när man enbart ska göra grundläggande saker.
2. cURL i PHP är fult och bygger helt och hållet på något fult C-liknande globalstate programmeringssätt, liksom mycket annat i C, vilket inte alls passar in i ett objektorienterat språk. Det finns bibliotek som tar hand om det på ett objektorienterat sätt, men det borde vara inbyggt.
3. Arbeta med både XPaths och cURL i PHP. DOMDocument används också, men då enbart för att sedan sättas in i DOMXpath, så inte någon direkt användning av funktionerna som finns där.
4. Att jobba med webscraping till att börja med. Har aldrig gjort något riktigt med det utan enbart väldigt små saker för att använda en gång eller så.