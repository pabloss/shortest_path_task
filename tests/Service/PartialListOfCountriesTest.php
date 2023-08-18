<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\CountryBordersService;
use PHPUnit\Framework\TestCase;

class PartialListOfCountriesTest extends TestCase
{
    private const COUNTRY_CODES = [
        "ABW",
        "AFG",
        "AGO",
        "AIA",
        "ALA",
        "ALB",
        "AND",
        "ARE",
        "ARG",
        "ARM",
        "ASM",
        "ATA",
        "ATF",
        "ATG",
        "AUS",
        "AUT",
        "AZE",
        "BDI",
        "BEL",
        "BEN",
        "BFA",
        "BGD",
        "BGR",
        "BHR",
        "BHS",
        "BIH",
        "BLM",
        "SHN",
        "BLR",
        "BLZ",
        "BMU",
        "BOL",
        "BES",
        "BRA",
        "BRB",
        "BRN",
        "BTN",
        "BVT",
        "BWA",
        "CAF",
        "CAN",
        "CCK",
        "CHE",
        "CHL",
        "CHN",
        "CIV",
        "CMR",
        "COD",
        "COG",
        "COK",
        "COL",
        "COM",
        "CPV",
        "CRI",
        "CUB",
        "CUW",
        "CXR",
        "CYM",
        "CYP",
        "CZE",
        "DEU",
        "DJI",
        "DMA",
        "DNK",
        "DOM",
        "DZA",
        "ECU",
        "EGY",
        "ERI",
        "ESH",
        "ESP",
        "EST",
        "ETH",
        "FIN",
        "FJI",
        "FLK",
        "FRA",
        "FRO",
        "FSM",
        "GAB",
        "GBR",
        "GEO",
        "GGY",
        "GHA",
        "GIB",
        "GIN",
        "GLP",
        "GMB",
        "GNB",
        "GNQ",
        "GRC",
        "GRD",
        "GRL",
        "GTM",
        "GUF",
        "GUM",
        "GUY",
        "HKG",
        "HMD",
        "HND",
        "HRV",
        "HTI",
        "HUN",
        "IDN",
        "IMN",
        "IND",
        "IOT",
        "IRL",
        "IRN",
        "IRQ",
        "ISL",
        "ISR",
        "ITA",
        "JAM",
        "JEY",
        "JOR",
        "JPN",
        "KAZ",
        "KEN",
        "KGZ",
        "KHM",
        "KIR",
        "KNA",
        "KOR",
        "UNK",
        "KWT",
        "LAO",
        "LBN",
        "LBR",
        "LBY",
        "LCA",
        "LIE",
        "LKA",
        "LSO",
        "LTU",
        "LUX",
        "LVA",
        "MAC",
        "MAF",
        "MAR",
        "MCO",
        "MDA",
        "MDG",
        "MDV",
        "MEX",
        "MHL",
        "MKD",
        "MLI",
        "MLT",
        "MMR",
        "MNE",
        "MNG",
        "MNP",
        "MOZ",
        "MRT",
        "MSR",
        "MTQ",
        "MUS",
        "MWI",
        "MYS",
        "MYT",
        "NAM",
        "NCL",
        "NER",
        "NFK",
        "NGA",
        "NIC",
        "NIU",
        "NLD",
        "NOR",
        "NPL",
        "NRU",
        "NZL",
        "OMN",
        "PAK",
        "PAN",
        "PCN",
        "PER",
        "PHL",
        "PLW",
        "PNG",
        "POL",
        "PRI",
        "PRK",
        "PRT",
        "PRY",
        "PSE",
        "PYF",
        "QAT",
        "REU",
        "ROU",
        "RUS",
        "RWA",
        "SAU",
        "SDN",
        "SEN",
        "SGP",
        "SGS",
        "SJM",
        "SLB",
        "SLE",
        "SLV",
        "SMR",
        "SOM",
        "SPM",
        "SRB",
        "SSD",
        "STP",
        "SUR",
        "SVK",
        "SVN",
        "SWE",
        "SWZ",
        "SXM",
        "SYC",
        "SYR",
        "TCA",
        "TCD",
        "TGO",
        "THA",
        "TJK",
        "TKL",
        "TKM",
        "TLS",
        "TON",
        "TTO",
        "TUN",
        "TUR",
        "TUV",
        "TWN",
        "TZA",
        "UGA",
        "UKR",
        "UMI",
        "URY",
        "USA",
        "UZB",
        "VAT",
        "VCT",
        "VEN",
        "VGB",
        "VIR",
        "VNM",
        "VUT",
        "WLF",
        "WSM",
        "YEM",
        "ZAF",
        "ZMB",
        "ZWE",
    ];

    public function testGetBorders()
    {
        $countries = file_get_contents('tests/resources/countries.json');
        $this->assertNotEmpty($countries);
        $countryBordersService = new CountryBordersService('tests/resources/countries.json');
        $this->assertEquals(
            [
                "AUT",
                "DEU",
                "POL",
                "SVK"
            ],
            $countryBordersService->getBorders("CZE")
        );
    }

    public function testGetRegion()
    {
        $countryBordersService = new CountryBordersService('tests/resources/countries.json');
        $this->assertEquals('Europe', $countryBordersService->getRegion('CZE'));
        $this->assertEquals('Europe', $countryBordersService->getRegion('ITA'));
    }

    public function testGetCountryCodes()
    {
        $countryBordersService = new CountryBordersService('tests/resources/countries.json');
        $this->assertEquals(self::COUNTRY_CODES, $countryBordersService->getCountryCodes());
    }

    public function testInitDijkstraMatrix()
    {
        $countryBordersService = new CountryBordersService('tests/resources/countries.json');
        $this->assertEquals(
            [
                'shortest_distance_from_origin' => PHP_INT_MAX,
                'previous_vertex' => null
            ],
            $countryBordersService->initDijkstraMatrix(
                $countryBordersService->getCountryCodes(),
                'CZE'
            )['FRA']
        );
    }

    public function testFindPath()
    {
        $countryBordersService = new CountryBordersService('tests/resources/countries.json');
        $this->assertEquals(
            [
                "FRA",
                "ITA",
                "SVN",
                "HUN",
                "SRB",
                "BGR",
            ],
            $countryBordersService->findPath('FRA', 'BGR')
        );
    }

    public function testNoPathFound()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("No land path from 'FRA' to 'CUB'");
        $countryBordersService = new CountryBordersService('tests/resources/countries.json');
        $countryBordersService->findPath('FRA', 'CUB');
    }
}
