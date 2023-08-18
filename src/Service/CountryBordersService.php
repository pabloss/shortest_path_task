<?php

declare(strict_types=1);

namespace App\Service;

class CountryBordersService
{
    private array $countries = [];
    public function __construct(string $countriesUrl)
    {
        foreach (json_decode(file_get_contents($countriesUrl), true) as $country) {
            if (!isset($country['cca3'], $country['borders'], $country['region'])) {
                throw new \InvalidArgumentException();
            }
            $this->countries[$country['cca3']] = ['borders' => $country['borders'], 'region' => $country['region']];
        }
    }
    public function getBorders(string $cca3): array
    {
        return $this->countries[$cca3]['borders'] ?? [];
    }

    public function getRegion(string $cca3): ?string
    {
        return $this->countries[$cca3]['region'] ?? null;
    }

    public function getCountryCodes()
    {
        return array_keys($this->countries);
    }

    public function findPath(string $orig, string $dest): array
    {
        $current = $orig;

        $visited = [];
        $unvisited = array_keys($this->countries);

        $dijkstraMatrix = $this->initDijkstraMatrix($unvisited, $current);

        /**
         * 
         array:6 [
                0 => "CZE"
                1 => "AUT"
                2 => "HUN"
                3 => "POL"
                4 => "SVK"
                5 => "SVN"
            ]
       
         * 
         * unvisted = [CZE, AUT, POL, SVK, SVN, HUN]
         * Jakie mamy węzły
         * ustawmy orign np. CZE na początek a dest np HUN na końcu
         * ustawmy 1000 000 jako shortest distance from origin
         * CZE sąsiaduje z z AUT, POL, i SVK z naszego unvisited
         * Previous vertex fior AUT, POL, i SVK to CZE
         * Shortest distance  from CZE => POL === 1, CZE => AUT === 1, CZE => SVK === 1,
         * $visited = [CZE]
         * $unvisted = [AUT, POL, SVK, SVN, HUN]
         * Startujemy terz z POL
         * z POL idziemy do SVK CZE => SVK + POL-1 === 2 > shortest known distance of CZE => SVK więć nie podmieniamy
         * $visited = [CZE, POL]
         * $uvisited = [AUT, SVK, SVN, HUN]
         * 
         * startujemy z AUT
         * AUT możemy do HUN, SVk, SVN
         * CZE => SVK ====1 + |AUT => SVK === 1| == 2 zostaje 1
         * CZE => SVN === 1 + |1| < 1000 000 podmieniamy na 2
         * CZE => HUN === 1 + |1| < 1000 pomieniamy na 2
         * 
         * $visited = [CZE, POL, AUT]
         * startujemy z SVK, ale wyrzuacmy, CZE, POL, AUT - zostaje HUN
         * CZE => HUN === 2 + |SVK => HUN === 1| === 3 > 2 zostaje 2
         * 
         * $visited =  [CZE, POL, AUT, SVK]
         * startumey z SVN nie mamy co , bo wzystko wyżucamy
         * 
         * 
         * $visited =  [CZE, POL, AUT, SVK, SVN]
         * startujemy z HUN i wyrzuamy, AUT SVK, SVMN z sąiadów
         * nie mamy nic 
         * 
         * koniec wypełnienia
         *    
         * 
         * starjuyem z dest w tabeli i sprawedzamy w tył previous vertext
         */


        while (!empty($unvisited)) {
            $current = $this->findCountryWithSmallestKnownDistance($unvisited, $dijkstraMatrix);

            if (null === $current) {
                break;
            }
            $unvisitedNeighbours = $this->getNextNeighbours($unvisited, $this->countries[$current]['borders']);
            $this->updateDijkstraMatrix($dijkstraMatrix, $current, $unvisitedNeighbours);
            $this->moveCurrentFromUnvisitedToVisited($current, $visited, $unvisited);
        }

        return $this->getPath($dijkstraMatrix, $orig, $dest);
    }

    public function findCountryWithSmallestKnownDistance(array $unvisited, array $dijkstraMatrix): ?string
    {
        $min = PHP_INT_MAX;
        $current = null;
        foreach ($unvisited as $country) {
            if ($dijkstraMatrix[$country]['shortest_distance_from_origin'] <= $min) {
                $min = $dijkstraMatrix[$country]['shortest_distance_from_origin'];
                $current = $country;
            }
        }

        return $current;
    }

    public function initDijkstraMatrix(array $unvisited, string $current): array
    {
        $dijkstraMatrix = array_fill_keys($unvisited, ['shortest_distance_from_origin' => PHP_INT_MAX, 'previous_vertex' => null]);
        $dijkstraMatrix[$current]['shortest_distance_from_origin'] = 0;
        return $dijkstraMatrix;
    }

    private function updateDijkstraMatrix(array &$dijkstraMatrix, string $current, array $unvisitedNeighbours): void
    {
        foreach ($unvisitedNeighbours as $unvisitedNeighbour) {
            $newDistance = $dijkstraMatrix[$current]['shortest_distance_from_origin'] + 1;

            if ($newDistance < $dijkstraMatrix[$unvisitedNeighbour]['shortest_distance_from_origin']) {
                $dijkstraMatrix[$unvisitedNeighbour]['shortest_distance_from_origin'] = $newDistance;
                $dijkstraMatrix[$unvisitedNeighbour]['previous_vertex'] = $current;
            }
        }
    }

    private function moveCurrentFromUnvisitedToVisited(string $current, array &$visited, array &$unvisited): void
    {
        if (!in_array($current, $visited)) {
            $visited[] = $current;
        }
        if (false !== ($key = array_search($current, $unvisited))) {
            unset($unvisited[$key]);
        }
    }

    private function getNextNeighbours(array $unvisited, array $borders): array
    {
        return array_values(array_intersect($unvisited, $borders));
    }

    private function getPath(array $matrix, string $orig, string $dest): array
    {
        $path = [];
        $last = $dest;

        do {
            $path[] = $last;
            $last = $matrix[$last]['previous_vertex'] ?? null;
        } while (null !== $last);

        if (!in_array($orig, $path)) {
            throw new \InvalidArgumentException("No land path from '$orig' to '$dest'");
        }

        return array_reverse($path);
    }
}
