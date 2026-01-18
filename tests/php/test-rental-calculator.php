<?php
/**
 * Mietwert-Rechner Berechnungstests
 *
 * Prüft, dass ALLE Parameter korrekt in die Berechnung einfließen
 *
 * @package Immobilien_Rechner_Pro
 * @subpackage Tests
 */

class Test_Rental_Calculator extends WP_UnitTestCase {

    private IRP_Calculator $calculator;

    public function setUp(): void {
        parent::setUp();
        $this->calculator = new IRP_Calculator();

        // Test-Matrix setzen
        update_option('irp_price_matrix', [
            'cities' => [
                [
                    'id' => 'test_city',
                    'name' => 'Test Stadt',
                    'base_price' => 10.00,
                    'size_degression' => 0.20,
                    'sale_factor' => 25,
                ]
            ],
            'condition_multipliers' => [
                'new' => 1.25,
                'renovated' => 1.10,
                'good' => 1.00,
                'needs_renovation' => 0.80,
            ],
            'type_multipliers' => [
                'apartment' => 1.00,
                'house' => 1.15,
                'commercial' => 0.85,
            ],
            'feature_premiums' => [
                'balcony' => 0.50,
                'terrace' => 0.75,
                'garden' => 1.00,
                'elevator' => 0.30,
                'garage' => 0.60,
            ],
            'location_ratings' => [
                1 => ['name' => 'Einfach', 'multiplier' => 0.85],
                2 => ['name' => 'Normal', 'multiplier' => 0.95],
                3 => ['name' => 'Gut', 'multiplier' => 1.00],
                4 => ['name' => 'Sehr gut', 'multiplier' => 1.10],
                5 => ['name' => 'Premium', 'multiplier' => 1.25],
            ],
            'age_multipliers' => [
                'from_2015' => ['multiplier' => 1.10, 'min_year' => 2015, 'max_year' => null],
                '1990_1999' => ['multiplier' => 1.00, 'min_year' => 1990, 'max_year' => 1999],
                '1960_1979' => ['multiplier' => 0.90, 'min_year' => 1960, 'max_year' => 1979],
            ],
        ]);
    }

    /**
     * Basis-Test: Grundberechnung ohne Modifikatoren
     */
    public function test_base_calculation() {
        $result = $this->calculator->calculate_rental_value([
            'size' => 70,  // Referenzgröße
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ]);

        // Bei 70m², Basispreis 10€, alle Faktoren = 1.0
        // Erwartete Miete: 70 * 10 = 700€
        $this->assertEquals(700.00, $result['monthly_rent']['estimate']);
        $this->assertEquals(10.00, $result['price_per_sqm']);
    }

    /**
     * Test: Wohnfläche beeinflusst Berechnung (Size Degression)
     */
    public function test_size_affects_calculation() {
        $base_params = [
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ];

        // Kleine Wohnung (50m²) sollte höheren m²-Preis haben
        $small = $this->calculator->calculate_rental_value(array_merge($base_params, ['size' => 50]));

        // Große Wohnung (120m²) sollte niedrigeren m²-Preis haben
        $large = $this->calculator->calculate_rental_value(array_merge($base_params, ['size' => 120]));

        $this->assertGreaterThan($large['price_per_sqm'], $small['price_per_sqm'],
            'FEHLER: Wohnfläche beeinflusst Preis/m² nicht korrekt (Size Degression fehlt)');
    }

    /**
     * Test: Zustand beeinflusst Berechnung
     */
    public function test_condition_affects_calculation() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ];

        $new = $this->calculator->calculate_rental_value(array_merge($base_params, ['condition' => 'new']));
        $renovation = $this->calculator->calculate_rental_value(array_merge($base_params, ['condition' => 'needs_renovation']));

        $this->assertGreaterThan($renovation['monthly_rent']['estimate'], $new['monthly_rent']['estimate'],
            'FEHLER: Zustand (condition) beeinflusst Berechnung nicht');

        // Prüfe exakten Faktor: new = 1.25, needs_renovation = 0.80
        $ratio = $new['monthly_rent']['estimate'] / $renovation['monthly_rent']['estimate'];
        $expected_ratio = 1.25 / 0.80;
        $this->assertEqualsWithDelta($expected_ratio, $ratio, 0.01,
            'FEHLER: Condition-Multiplikatoren werden nicht korrekt angewendet');
    }

    /**
     * Test: Immobilientyp beeinflusst Berechnung
     */
    public function test_property_type_affects_calculation() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'location_rating' => 3,
        ];

        $apartment = $this->calculator->calculate_rental_value(array_merge($base_params, ['property_type' => 'apartment']));
        $house = $this->calculator->calculate_rental_value(array_merge($base_params, ['property_type' => 'house']));
        $commercial = $this->calculator->calculate_rental_value(array_merge($base_params, ['property_type' => 'commercial']));

        $this->assertGreaterThan($apartment['monthly_rent']['estimate'], $house['monthly_rent']['estimate'],
            'FEHLER: property_type=house sollte höher sein als apartment');
        $this->assertGreaterThan($commercial['monthly_rent']['estimate'], $apartment['monthly_rent']['estimate'],
            'FEHLER: property_type=commercial sollte niedriger sein als apartment');
    }

    /**
     * Test: Features beeinflussen Berechnung
     */
    public function test_features_affect_calculation() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ];

        $no_features = $this->calculator->calculate_rental_value(array_merge($base_params, ['features' => []]));
        $with_features = $this->calculator->calculate_rental_value(array_merge($base_params, [
            'features' => ['balcony', 'terrace', 'garage']
        ]));

        $this->assertGreaterThan($no_features['monthly_rent']['estimate'], $with_features['monthly_rent']['estimate'],
            'FEHLER: Features beeinflussen Berechnung nicht');

        // Erwarteter Aufschlag: balcony(0.50) + terrace(0.75) + garage(0.60) = 1.85 €/m²
        $expected_premium = (0.50 + 0.75 + 0.60) * 70;
        $actual_diff = $with_features['monthly_rent']['estimate'] - $no_features['monthly_rent']['estimate'];
        $this->assertEqualsWithDelta($expected_premium, $actual_diff, 1.00,
            'FEHLER: Feature-Premiums werden nicht korrekt addiert');
    }

    /**
     * Test: Baujahr beeinflusst Berechnung
     */
    public function test_year_built_affects_calculation() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ];

        $new_building = $this->calculator->calculate_rental_value(array_merge($base_params, ['year_built' => 2020]));
        $old_building = $this->calculator->calculate_rental_value(array_merge($base_params, ['year_built' => 1970]));

        $this->assertGreaterThan($old_building['monthly_rent']['estimate'], $new_building['monthly_rent']['estimate'],
            'FEHLER: Baujahr (year_built) beeinflusst Berechnung nicht');

        // Prüfe Faktoren: 2020 = 1.10, 1970 = 0.90
        $ratio = $new_building['monthly_rent']['estimate'] / $old_building['monthly_rent']['estimate'];
        $expected_ratio = 1.10 / 0.90;
        $this->assertEqualsWithDelta($expected_ratio, $ratio, 0.01,
            'FEHLER: Age-Multiplikatoren werden nicht korrekt angewendet');
    }

    /**
     * Test: Location Rating beeinflusst Berechnung
     */
    public function test_location_rating_affects_calculation() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
        ];

        $simple = $this->calculator->calculate_rental_value(array_merge($base_params, ['location_rating' => 1]));
        $premium = $this->calculator->calculate_rental_value(array_merge($base_params, ['location_rating' => 5]));

        $this->assertGreaterThan($simple['monthly_rent']['estimate'], $premium['monthly_rent']['estimate'],
            'FEHLER: location_rating beeinflusst Berechnung nicht');

        // Prüfe Faktoren: simple = 0.85, premium = 1.25
        $ratio = $premium['monthly_rent']['estimate'] / $simple['monthly_rent']['estimate'];
        $expected_ratio = 1.25 / 0.85;
        $this->assertEqualsWithDelta($expected_ratio, $ratio, 0.01,
            'FEHLER: Location-Multiplikatoren werden nicht korrekt angewendet');
    }

    /**
     * Test: Stadt (city_id) beeinflusst Berechnung
     */
    public function test_city_affects_calculation() {
        // Zweite Stadt mit anderem Basispreis
        $matrix = get_option('irp_price_matrix');
        $matrix['cities'][] = [
            'id' => 'expensive_city',
            'name' => 'Teure Stadt',
            'base_price' => 15.00,
            'size_degression' => 0.20,
        ];
        update_option('irp_price_matrix', $matrix);

        $calculator = new IRP_Calculator();

        $base_params = [
            'size' => 70,
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ];

        $cheap = $calculator->calculate_rental_value(array_merge($base_params, ['city_id' => 'test_city']));
        $expensive = $calculator->calculate_rental_value(array_merge($base_params, ['city_id' => 'expensive_city']));

        $this->assertGreaterThan($cheap['monthly_rent']['estimate'], $expensive['monthly_rent']['estimate'],
            'FEHLER: city_id beeinflusst Berechnung nicht');

        // Erwartetes Verhältnis: 15/10 = 1.5
        $ratio = $expensive['monthly_rent']['estimate'] / $cheap['monthly_rent']['estimate'];
        $this->assertEqualsWithDelta(1.5, $ratio, 0.01,
            'FEHLER: Stadt-Basispreise werden nicht korrekt angewendet');
    }

    /**
     * Test: Parameter "rooms" ist absichtlich Lead-Data-Only
     *
     * Der Parameter "rooms" wird im Frontend abgefragt, fließt aber absichtlich
     * NICHT in die Berechnung ein. Die Zimmeranzahl dient nur der Lead-Qualifizierung
     * für die Maklerberatung. Die Wohnfläche (size) ist der relevante Faktor.
     *
     * @see class-calculator.php:243 - $rooms wird gelesen aber nicht verwendet
     * @see docs/API.md - rooms ist als "Lead data only" dokumentiert
     */
    public function test_rooms_is_lead_data_only() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ];

        // Berechnung mit verschiedenen Zimmeranzahlen
        $with_2_rooms = $this->calculator->calculate_rental_value(array_merge($base_params, ['rooms' => 2]));
        $with_5_rooms = $this->calculator->calculate_rental_value(array_merge($base_params, ['rooms' => 5]));

        // Ergebnis muss identisch sein - rooms hat keinen Einfluss (by design)
        $this->assertEquals(
            $with_2_rooms['monthly_rent']['estimate'],
            $with_5_rooms['monthly_rent']['estimate'],
            'Parameter "rooms" sollte keinen Einfluss auf die Berechnung haben (Lead data only)'
        );
    }
}
