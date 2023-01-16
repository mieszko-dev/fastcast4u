1. Aplikacja wykorzystuje Laravel Sail, w którym mamy od razu m.in. DB, Mailhog. Przy uruchamianiu aplikacji to może być
   przydatne: https://laravel.com/docs/9.x/sail#installing-composer-dependencies-for-existing-projects
2. Każdy krok procesu rejestracji to oddzielny kontroler i form request.
3. W pierwszym kroku nadajemy potencjalnemu użytkownikowi `registration_token`, który wykorzystujemy następnie w
   kolejnych krokach. W odpowiedzi na pierwszy krok użytkownik otrzymuje `registration_token`, który należy wstawić
   do `body` każdego następnego requestu w pozostałych krokach.
4. W tabeli `RegistrationSteps` zapisujemy, które kroki są dostępne dla danego `registration_token`. Dzięki temu
   użytkownik nie może przejść do kolejnego kroku, dopóki nie zostanie zakończony obecny etap.
5. Ze względu na to, że szyfrowanie symetryczne uniemożliwia nam sprawdzanie unikalności numeru telefonu (oprócz
   iteracyjnego porównania zaszyfrowanych wartości) dodałem w tabeli `users` kolumnę `phone_hash`.
   Wykorzystałem `hash_hmac`, żeby utworzyć hash, który można porównywać (ponieważ dla danego numeru zwraca zawsze taką
   samą wartość).
6. Wychodzące maile może podejrzeć w mailhog. Sail domyślnie ustawia go na `http://0.0.0.0:8025/`.
7. Notyfikacje (z kodamy weryfikacyjnymi) są kolejkowane, ale kolejka jest ustawiona na `sync`. Domyślnie można ustawić
   kolejkę z wysokim priorytetem.
8. Wysyłka maili jest tylko lokalnie na mailhoga, ponieważ SES wymaga weryfikacji maila itp.
8. Ustawiłem dla kodu wysyłanego SMS również wysyłkę mailem. Zakładam, że tutaj będzie podobny problem jak z SES,
   dlatego do testów zostawiam email `(User.php:73)`
9. Konta użytkowników, którzy nie dokończyli procesu rejestracji można usuwać cyklicznie.
9. Gdyby coś wymagało omówienia to proszę o informację :)
