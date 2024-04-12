<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class HomePresenter extends Nette\Application\UI\Presenter
{
    public const jokes = 'https://www.digilabs.cz/hiring/data.php';

    /**
     * @return void
     */
    public function renderDefault(): void
    {

    }

    /**
     * @return void
     */
    public function renderActionOne(): void
    {
        $jokes = $this->downloadJokes();
        if (count($jokes)) {
            $countJokes = count($jokes);

            do {
                $selectJokes = [];
                $resultJoke = $jokes[rand(0, $countJokes - 1)];
                $selectJokes[] = $resultJoke;
            } while (strlen($resultJoke->joke) > 120);

            $joke = $this->explodeJoke($selectJokes);

            $this->template->title = 'Akce 1';
            $this->template->condition = 'Náhodné vybrání jednoho z vtipů o Chucku Norrisovi (atribut "joke"), jehož délka nepřesahuje 120
znaků a jeho zobrazení ve formě meme, a to takovým způsobem, že první polovina vtipu bude
zobrazení ve vrchní části obrázku a druhá polovina ve spodní. Jako obrázek meme použijte:
https://www.digilabs.cz/hiring/chuck.jpg';
            $this->template->jokes = $joke;
            $this->view = 'meme';
        } else {
            $this->view = 'error';
        }
    }

    /**
     * @return void
     */
    public function renderActionTwo(): void
    {
        $jokes = $this->downloadJokes();
        if (count($jokes)) {
            $selectJokes = [];
            foreach ($jokes as $j) {
                $names = explode(' ', $j->name);
                $firstName = $names[0];
                $lastName = count($names) === 2 ? $names[1] : $names[2];

                if (substr($firstName, 0, 1) == substr($lastName, 0, 1)) {
                    $selectJokes[] = $j;
                }
            }

            $this->template->title = 'Akce 2';
            $this->template->condition = 'Vyfiltrování a prezentace všech záznamů, kterým jejichž křestní jméno a příjmení začínají na jedno
a to samé písmeno – iniciály (atribut "name").';
            $this->template->jokes = $selectJokes;
            $this->view = 'jokes';
        } else {
            $this->view = 'error';
        }
    }

    /**
     * @return void
     */
    public function renderActionThree(): void
    {
        $jokes = $this->downloadJokes();
        if (count($jokes)) {
            $selectJokes = [];
            foreach ($jokes as $j) {
                if ($j->firstNumber % 2 == 0) {
                    if (($j->firstNumber / $j->secondNumber) === $j->thirdNumber) {
                        $selectJokes[] = $j;
                    }
                }
            }

            $this->template->title = 'Akce 3';
            $this->template->condition = 'Vyfiltrování a prezentace všech záznamů, které mají správně výpočet "firstNumber" /
"secondNumber" = "thirdNumber" a zároveň je "firstNumber" sudé.';
            $this->template->jokes = $selectJokes;
            $this->view = 'jokes';
        } else {
            $this->view = 'error';
        }
    }

    /**
     * hodnoty v createdAT mají rozdílné TimeZone, ale nemusíme řešit, protože kontrolujeme datum
     *
     * @return void
     * @throws \Exception
     */
    public function renderActionFour(): void
    {
        $jokes = $this->downloadJokes();
        if (count($jokes)) {
            $currentDate = new \DateTime('now');
            $todayMinusMonth = $currentDate->modify('-1 month')->format('Y-m-d');
            $todayPlusMonth = $currentDate->modify('+1 month')->format('Y-m-d');

            $selectJokes = [];
            foreach ($jokes as $j) {
                $jokeDate = new \DateTime($j->createdAt);
                $jokeDateFormat = $jokeDate->format('Y-m-d');

                if ($todayMinusMonth < $jokeDateFormat && $todayPlusMonth > $jokeDateFormat) {
                    $selectJokes[] = $j;
                }
            }

            $this->template->title = 'Akce 4';
            $this->template->condition = 'Vyfiltrování a prezentace všech záznamů, které mají atribut "createdAt" v intervalu -1 a +1 měsíc
od aktuálního data.';
            $this->template->jokes = $selectJokes;
            $this->view = 'jokes';
        } else {
            $this->view = 'error';
        }
    }

    /**
     * @return void
     */
    public function renderActionFive(): void
    {
        $jokes = $this->downloadJokes();
        if (count($jokes)) {
            $selectJokes = [];
            foreach ($jokes as $j) {
                $parts = explode('=', $j->calculation);

                if (strlen($parts[0]) > strlen($parts[1])) {
                    $math = $parts[0];
                    $res = $parts[1];
                } else {
                    $math = $parts[1];
                    $res = $parts[0];
                }

                if (preg_match('/(\d+)(?:\s*)([\+\-\*\/])(?:\s*)(\d+)/', $math, $matches) !== false) {
                    $operator = $matches[2];

                    switch ($operator) {
                        case '+':
                            $calc = $matches[1] + $matches[3];
                            break;
                        case '-':
                            $calc = $matches[1] - $matches[3];
                            break;
                        case '*':
                            $calc = $matches[1] * $matches[3];
                            break;
                        case '/':
                            $calc = $matches[1] / $matches[3];
                            break;
                    }
                }
                if ($res == $calc) {
                    $selectJokes[] = $j;
                }
            }

            $this->template->title = 'Akce 5';
            $this->template->condition = 'Vyfiltrování a prezentace všech záznamů, které mají správný výpočet v atributu "calculation" – bez
použití funkce "eval()".';
            $this->template->jokes = $selectJokes;
            $this->view = 'jokes';
        } else {
            $this->view = 'error';
        }
    }

    /**
     * stažení všech vtipů
     * @return array
     */
    private function downloadJokes(): array
    {
        $json = file_get_contents(self::jokes);
        $arr = json_decode($json);

        return $arr;
    }

    /**
     * Rozdělení vtipu na dvě poloviny
     *
     * @param array $selectJokes
     * @return array
     */
    private function explodeJoke(array $selectJokes): array
    {
        $jokes = [];
        foreach ($selectJokes as $joke) {
            $words = explode(' ', $joke->joke);
            $halfWords = count($words) / 2;

            //pokud je lichý počet slov tak první polovina bude mít více slov
            if (is_float($halfWords)) {
                $halfWords = (int)$halfWords + 1;
            }

            $chunk = array_chunk($words, $halfWords);
            $jokes[] = ['part1' => implode(' ', $chunk[0]), 'part2' => implode(' ', $chunk[1])];
        }

        return $jokes;
    }

}
