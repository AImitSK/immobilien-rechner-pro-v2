<?php
/**
 * Verkaufswert-Rechner Berechnungstests
 *
 * Prüft, dass ALLE Parameter korrekt in die Berechnung einfließen
 * Testet alle 3 Bewertungsverfahren: Wohnung, Haus, Grundstück
 *
 * @package Immobilien_Rechner_Pro
 * @subpackage Tests
 */

class Test_Sale_Calculator extends WP_UnitTestCase {

    private IRP_Sale_Calculator $calculator;

    public function setUp(): void {
        parent::setUp();

        // Test-Matrix setzen
        update_option('irp_price_matrix', [
            'cities' => [
                [
                    'id' => 'test_city',
                    'name' => 'Test Stadt',
                    'land_price_per_sqm' => 200,
                    'building_price_per_sqm' => 2500,
                    'apartment_price_per_sqm' => 3000,
                    'market_adjustment_factor' => 1.00,
                ]
            ],
            'location_ratings' => [
                1 => ['name' => 'Einfach', 'multiplier' => 0.85],
                2 => ['name' => 'Normal', 'multiplier' => 0.95],
                3 => ['name' => 'Gut', 'multiplier' => 1.00],
                4 => ['name' => 'Sehr gut', 'multiplier' => 1.10],
                5 => ['name' => 'Premium', 'multiplier' => 1.25],
            ],
        ]);

        update_option('irp_sale_value_settings', [
            'house_type_multipliers' => [
                'single_family' => ['name' => 'Einfamilienhaus', 'multiplier' => 1.00],
                'multi_family' => ['name' => 'Mehrfamilienhaus', 'multiplier' => 1.15],
                'semi_detached' => ['name' => 'Doppelhaushälfte', 'multiplier' => 0.95],
            ],
            'quality_multipliers' => [
                'simple' => ['name' => 'Einfach', 'multiplier' => 0.85],
                'normal' => ['name' => 'Normal', 'multiplier' => 1.00],
                'upscale' => ['name' => 'Gehoben', 'multiplier' => 1.15],
                'luxury' => ['name' => 'Luxuriös', 'multiplier' => 1.35],
            ],
            'modernization_year_shift' => [
                '1-3_years' => ['name' => 'Vor 1-3 Jahren', 'years' => 15],
                '4-9_years' => ['name' => 'Vor 4-9 Jahren', 'years' => 10],
                'never' => ['name' => 'Noch nie', 'years' => 0],
            ],
            'age_depreciation' => [
                'rate_per_year' => 0.01,
                'max_depreciation' => 0.40,
                'base_year' => 2025,
            ],
            'features' => [
                'garage' => ['name' => 'Garage', 'value' => 15000, 'type' => 'exterior'],
                'garden' => ['name' => 'Garten', 'value' => 8000, 'type' => 'exterior'],
                'elevator' => ['name' => 'Aufzug', 'value' => 20000, 'type' => 'interior'],
            ],
        ]);

        $this->calculator = new IRP_Sale_Calculator();
    }

    // ========================================================================
    // WOHNUNGS-TESTS (Vergleichswertverfahren)
    // ========================================================================

    /**
     * Test: Basis-Berechnung für Wohnung
     */
    public function test_apartment_base_calculation() {
        $result = $this->calculator->calculate([
            'property_type' => 'apartment',
            'city_id' => 'test_city',
            'living_space' => 80,
            'build_year' => 2025,  // Neubau = keine Alterswertminderung
            'modernization' => 'never',
            'quality' => 'normal',
            'location_rating' => 3,
            'features' => [],
        ]);

        // 80m² * 3000€/m² = 240.000€
        $this->assertEquals('apartment', $result['calculation_type']);
        $this->assertEquals(240000, $result['price_estimate']);
    }

    /**
     * Test: Wohnfläche beeinflusst Berechnung
     */
    public function test_apartment_living_space_affects_calculation() {
        $base_params = [
            'property_type' => 'apartment',
            'city_id' => 'test_city',
            'build_year' => 2025,
            'modernization' => 'never',
            'quality' => 'normal',
            'location_rating' => 3,
            'features' => [],
        ];

        $small = $this->calculator->calculate(array_merge($base_params, ['living_space' => 50]));
        $large = $this->calculator->calculate(array_merge($base_params, ['living_space' => 100]));

        $this->assertGreaterThan($small['price_estimate'], $large['price_estimate'],
            'FEHLER: living_space beeinflusst Wohnungswert nicht');

        // Verhältnis sollte 2:1 sein
        $ratio = $large['price_estimate'] / $small['price_estimate'];
        $this->assertEqualsWithDelta(2.0, $ratio, 0.01);
    }

    /**
     * Test: Qualität beeinflusst Wohnungswert
     */
    public function test_apartment_quality_affects_calculation() {
        $base_params = [
            'property_type' => 'apartment',
            'city_id' => 'test_city',
            'living_space' => 80,
            'build_year' => 2025,
            'modernization' => 'never',
            'location_rating' => 3,
            'features' => [],
        ];

        $simple = $this->calculator->calculate(array_merge($base_params, ['quality' => 'simple']));
        $luxury = $this->calculator->calculate(array_merge($base_params, ['quality' => 'luxury']));

        $this->assertGreaterThan($simple['price_estimate'], $luxury['price_estimate'],
            'FEHLER: quality beeinflusst Wohnungswert nicht');

        // Verhältnis: luxury(1.35) / simple(0.85)
        $ratio = $luxury['price_estimate'] / $simple['price_estimate'];
        $expected_ratio = 1.35 / 0.85;
        $this->assertEqualsWithDelta($expected_ratio, $ratio, 0.05);
    }

    /**
     * Test: Location Rating beeinflusst Wohnungswert
     */
    public function test_apartment_location_affects_calculation() {
        $base_params = [
            'property_type' => 'apartment',
            'city_id' => 'test_city',
            'living_space' => 80,
            'build_year' => 2025,
            'modernization' => 'never',
            'quality' => 'normal',
            'features' => [],
        ];

        $simple = $this->calculator->calculate(array_merge($base_params, ['location_rating' => 1]));
        $premium = $this->calculator->calculate(array_merge($base_params, ['location_rating' => 5]));

        $this->assertGreaterThan($simple['price_estimate'], $premium['price_estimate'],
            'FEHLER: location_rating beeinflusst Wohnungswert nicht');
    }

    /**
     * Test: Baujahr/Alter beeinflusst Wohnungswert
     */
    public function test_apartment_build_year_affects_calculation() {
        $base_params = [
            'property_type' => 'apartment',
            'city_id' => 'test_city',
            'living_space' => 80,
            'modernization' => 'never',
            'quality' => 'normal',
            'location_rating' => 3,
            'features' => [],
        ];

        $new_build = $this->calculator->calculate(array_merge($base_params, ['build_year' => 2025]));
        $old_build = $this->calculator->calculate(array_merge($base_params, ['build_year' => 1970]));

        $this->assertGreaterThan($old_build['price_estimate'], $new_build['price_estimate'],
            'FEHLER: build_year beeinflusst Wohnungswert nicht');
    }

    /**
     * Test: Modernisierung beeinflusst Alterswertminderung
     */
    public function test_apartment_modernization_affects_calculation() {
        $base_params = [
            'property_type' => 'apartment',
            'city_id' => 'test_city',
            'living_space' => 80,
            'build_year' => 1990,
            'quality' => 'normal',
            'location_rating' => 3,
            'features' => [],
        ];

        $not_modernized = $this->calculator->calculate(array_merge($base_params, ['modernization' => 'never']));
        $recently_modernized = $this->calculator->calculate(array_merge($base_params, ['modernization' => '1-3_years']));

        $this->assertGreaterThan($not_modernized['price_estimate'], $recently_modernized['price_estimate'],
            'FEHLER: modernization beeinflusst Alterswertminderung nicht');
    }

    /**
     * Test: Features beeinflussen Wohnungswert
     */
    public function test_apartment_features_affect_calculation() {
        $base_params = [
            'property_type' => 'apartment',
            'city_id' => 'test_city',
            'living_space' => 80,
            'build_year' => 2025,
            'modernization' => 'never',
            'quality' => 'normal',
            'location_rating' => 3,
        ];

        $no_features = $this->calculator->calculate(array_merge($base_params, ['features' => []]));
        $with_features = $this->calculator->calculate(array_merge($base_params, ['features' => ['garage', 'elevator']]));

        $this->assertGreaterThan($no_features['price_estimate'], $with_features['price_estimate'],
            'FEHLER: features beeinflussen Wohnungswert nicht');

        // Erwarteter Aufschlag: garage(15000) + elevator(20000) = 35000
        $expected_diff = 35000;
        $actual_diff = $with_features['price_estimate'] - $no_features['price_estimate'];
        $this->assertEqualsWithDelta($expected_diff, $actual_diff, 1000);
    }

    // ========================================================================
    // HAUS-TESTS (Sachwertverfahren)
    // ========================================================================

    /**
     * Test: Basis-Berechnung für Haus (Sachwertverfahren)
     */
    public function test_house_base_calculation() {
        $result = $this->calculator->calculate([
            'property_type' => 'house',
            'city_id' => 'test_city',
            'living_space' => 150,
            'land_size' => 500,
            'house_type' => 'single_family',
            'build_year' => 2025,
            'modernization' => 'never',
            'quality' => 'normal',
            'location_rating' => 3,
            'features' => [],
        ]);

        $this->assertEquals('house', $result['calculation_type']);
        // Bodenwert: 500m² * 200€ = 100.000€
        // Gebäudewert: 150m² * 2500€ = 375.000€
        // Gesamt: 475.000€
        $this->assertEquals(475000, $result['price_estimate']);
    }

    /**
     * Test: Grundstücksgröße beeinflusst Hauswert
     */
    public function test_house_land_size_affects_calculation() {
        $base_params = [
            'property_type' => 'house',
            'city_id' => 'test_city',
            'living_space' => 150,
            'house_type' => 'single_family',
            'build_year' => 2025,
            'modernization' => 'never',
            'quality' => 'normal',
            'location_rating' => 3,
            'features' => [],
        ];

        $small_land = $this->calculator->calculate(array_merge($base_params, ['land_size' => 300]));
        $large_land = $this->calculator->calculate(array_merge($base_params, ['land_size' => 800]));

        $this->assertGreaterThan($small_land['price_estimate'], $large_land['price_estimate'],
            'FEHLER: land_size beeinflusst Hauswert nicht');
    }

    /**
     * Test: Haustyp beeinflusst Berechnung
     */
    public function test_house_type_affects_calculation() {
        $base_params = [
            'property_type' => 'house',
            'city_id' => 'test_city',
            'living_space' => 150,
            'land_size' => 500,
            'build_year' => 2025,
            'modernization' => 'never',
            'quality' => 'normal',
            'location_rating' => 3,
            'features' => [],
        ];

        $single = $this->calculator->calculate(array_merge($base_params, ['house_type' => 'single_family']));
        $multi = $this->calculator->calculate(array_merge($base_params, ['house_type' => 'multi_family']));
        $semi = $this->calculator->calculate(array_merge($base_params, ['house_type' => 'semi_detached']));

        // Mehrfamilienhaus > Einfamilienhaus > Doppelhaushälfte
        $this->assertGreaterThan($single['price_estimate'], $multi['price_estimate'],
            'FEHLER: house_type=multi_family sollte höher sein als single_family');
        $this->assertGreaterThan($semi['price_estimate'], $single['price_estimate'],
            'FEHLER: house_type=single_family sollte höher sein als semi_detached');
    }

    /**
     * Test: Qualität beeinflusst Hauswert
     */
    public function test_house_quality_affects_calculation() {
        $base_params = [
            'property_type' => 'house',
            'city_id' => 'test_city',
            'living_space' => 150,
            'land_size' => 500,
            'house_type' => 'single_family',
            'build_year' => 2025,
            'modernization' => 'never',
            'location_rating' => 3,
            'features' => [],
        ];

        $simple = $this->calculator->calculate(array_merge($base_params, ['quality' => 'simple']));
        $luxury = $this->calculator->calculate(array_merge($base_params, ['quality' => 'luxury']));

        $this->assertGreaterThan($simple['price_estimate'], $luxury['price_estimate'],
            'FEHLER: quality beeinflusst Hauswert nicht');
    }

    // ========================================================================
    // GRUNDSTÜCKS-TESTS (Bodenwert)
    // ========================================================================

    /**
     * Test: Basis-Berechnung für Grundstück
     */
    public function test_land_base_calculation() {
        $result = $this->calculator->calculate([
            'property_type' => 'land',
            'city_id' => 'test_city',
            'land_size' => 1000,
            'location_rating' => 3,
        ]);

        $this->assertEquals('land', $result['calculation_type']);
        // 1000m² * 200€/m² = 200.000€
        $this->assertEquals(200000, $result['price_estimate']);
    }

    /**
     * Test: Grundstücksgröße beeinflusst Wert
     */
    public function test_land_size_affects_calculation() {
        $base_params = [
            'property_type' => 'land',
            'city_id' => 'test_city',
            'location_rating' => 3,
        ];

        $small = $this->calculator->calculate(array_merge($base_params, ['land_size' => 500]));
        $large = $this->calculator->calculate(array_merge($base_params, ['land_size' => 1500]));

        $ratio = $large['price_estimate'] / $small['price_estimate'];
        $this->assertEqualsWithDelta(3.0, $ratio, 0.01,
            'FEHLER: land_size beeinflusst Grundstückswert nicht korrekt');
    }

    /**
     * Test: Location Rating beeinflusst Grundstückswert
     */
    public function test_land_location_affects_calculation() {
        $base_params = [
            'property_type' => 'land',
            'city_id' => 'test_city',
            'land_size' => 1000,
        ];

        $simple = $this->calculator->calculate(array_merge($base_params, ['location_rating' => 1]));
        $premium = $this->calculator->calculate(array_merge($base_params, ['location_rating' => 5]));

        $this->assertGreaterThan($simple['price_estimate'], $premium['price_estimate'],
            'FEHLER: location_rating beeinflusst Grundstückswert nicht');
    }

    // ========================================================================
    // STADT-TESTS
    // ========================================================================

    /**
     * Test: Stadt (city_id) beeinflusst Berechnung
     */
    public function test_city_affects_calculation() {
        // Zweite Stadt mit höheren Preisen
        $matrix = get_option('irp_price_matrix');
        $matrix['cities'][] = [
            'id' => 'expensive_city',
            'name' => 'Teure Stadt',
            'land_price_per_sqm' => 400,
            'building_price_per_sqm' => 4000,
            'apartment_price_per_sqm' => 5000,
            'market_adjustment_factor' => 1.00,
        ];
        update_option('irp_price_matrix', $matrix);

        $calculator = new IRP_Sale_Calculator();

        $base_params = [
            'property_type' => 'apartment',
            'living_space' => 80,
            'build_year' => 2025,
            'modernization' => 'never',
            'quality' => 'normal',
            'location_rating' => 3,
            'features' => [],
        ];

        $cheap = $calculator->calculate(array_merge($base_params, ['city_id' => 'test_city']));
        $expensive = $calculator->calculate(array_merge($base_params, ['city_id' => 'expensive_city']));

        $this->assertGreaterThan($cheap['price_estimate'], $expensive['price_estimate'],
            'FEHLER: city_id beeinflusst Berechnung nicht');
    }
}
