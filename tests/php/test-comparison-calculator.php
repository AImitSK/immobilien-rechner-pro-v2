<?php
/**
 * Vergleichs-Rechner Berechnungstests (Verkaufen vs. Vermieten)
 *
 * Prüft, dass ALLE Parameter korrekt in die Berechnung einfließen
 *
 * @package Immobilien_Rechner_Pro
 * @subpackage Tests
 */

class Test_Comparison_Calculator extends WP_UnitTestCase {

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
                'good' => 1.00,
                'needs_renovation' => 0.80,
            ],
            'type_multipliers' => [
                'apartment' => 1.00,
                'house' => 1.15,
            ],
            'feature_premiums' => [
                'balcony' => 0.50,
                'garage' => 0.60,
            ],
            'location_ratings' => [
                1 => ['name' => 'Einfach', 'multiplier' => 0.85],
                3 => ['name' => 'Gut', 'multiplier' => 1.00],
                5 => ['name' => 'Premium', 'multiplier' => 1.25],
            ],
            'age_multipliers' => [
                'from_2015' => ['multiplier' => 1.10, 'min_year' => 2015, 'max_year' => null],
                '1990_1999' => ['multiplier' => 1.00, 'min_year' => 1990, 'max_year' => 1999],
            ],
            'appreciation_rate' => 2.0,
            'rent_increase_rate' => 1.5,
        ]);

        // Settings setzen
        update_option('irp_settings', [
            'default_maintenance_rate' => 1.5,
            'default_vacancy_rate' => 3,
            'default_broker_commission' => 3.57,
        ]);
    }

    /**
     * Test: Basis-Berechnung liefert alle erwarteten Felder
     */
    public function test_comparison_returns_complete_structure() {
        $result = $this->calculator->calculate_comparison([
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
            'property_value' => 200000,
            'remaining_mortgage' => 50000,
            'mortgage_rate' => 3.5,
            'holding_period_years' => 5,
            'expected_appreciation' => 2.0,
        ]);

        // Struktur prüfen
        $this->assertArrayHasKey('rental', $result);
        $this->assertArrayHasKey('sale', $result);
        $this->assertArrayHasKey('rental_scenario', $result);
        $this->assertArrayHasKey('yields', $result);
        $this->assertArrayHasKey('vervielfaeltiger', $result);
        $this->assertArrayHasKey('break_even_year', $result);
        $this->assertArrayHasKey('projection', $result);
        $this->assertArrayHasKey('recommendation', $result);
    }

    /**
     * Test: Mietwert-Parameter fließen korrekt ein
     */
    public function test_rental_parameters_affect_comparison() {
        $base_params = [
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
            'property_value' => 200000,
            'remaining_mortgage' => 0,
            'mortgage_rate' => 3.5,
            'holding_period_years' => 5,
        ];

        // Größere Wohnung = höhere Miete = höhere Rendite
        $small = $this->calculator->calculate_comparison(array_merge($base_params, ['size' => 50]));
        $large = $this->calculator->calculate_comparison(array_merge($base_params, ['size' => 100]));

        $this->assertGreaterThan(
            $small['rental_scenario']['gross_annual_rent'],
            $large['rental_scenario']['gross_annual_rent'],
            'FEHLER: size beeinflusst Mietberechnung im Vergleich nicht'
        );
    }

    /**
     * Test: Immobilienwert beeinflusst Verkaufsszenario
     */
    public function test_property_value_affects_sale_scenario() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
            'remaining_mortgage' => 0,
            'mortgage_rate' => 3.5,
            'holding_period_years' => 5,
        ];

        $low_value = $this->calculator->calculate_comparison(array_merge($base_params, ['property_value' => 150000]));
        $high_value = $this->calculator->calculate_comparison(array_merge($base_params, ['property_value' => 300000]));

        $this->assertGreaterThan(
            $low_value['sale']['net_proceeds'],
            $high_value['sale']['net_proceeds'],
            'FEHLER: property_value beeinflusst Verkaufserlös nicht'
        );

        // Verhältnis sollte ca. 2:1 sein
        $ratio = $high_value['sale']['net_proceeds'] / $low_value['sale']['net_proceeds'];
        $this->assertEqualsWithDelta(2.0, $ratio, 0.1);
    }

    /**
     * Test: Restschuld reduziert Verkaufserlös
     */
    public function test_remaining_mortgage_reduces_net_proceeds() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
            'property_value' => 200000,
            'mortgage_rate' => 3.5,
            'holding_period_years' => 5,
        ];

        $no_mortgage = $this->calculator->calculate_comparison(array_merge($base_params, ['remaining_mortgage' => 0]));
        $with_mortgage = $this->calculator->calculate_comparison(array_merge($base_params, ['remaining_mortgage' => 80000]));

        $this->assertGreaterThan(
            $with_mortgage['sale']['net_proceeds'],
            $no_mortgage['sale']['net_proceeds'],
            'FEHLER: remaining_mortgage reduziert Verkaufserlös nicht'
        );

        // Differenz sollte ca. 80.000€ sein
        $diff = $no_mortgage['sale']['net_proceeds'] - $with_mortgage['sale']['net_proceeds'];
        $this->assertEqualsWithDelta(80000, $diff, 100);
    }

    /**
     * Test: Hypothekenzins beeinflusst Nettomieteinnahmen
     */
    public function test_mortgage_rate_affects_net_income() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
            'property_value' => 200000,
            'remaining_mortgage' => 100000,  // Hypothek vorhanden
            'holding_period_years' => 5,
        ];

        $low_rate = $this->calculator->calculate_comparison(array_merge($base_params, ['mortgage_rate' => 2.0]));
        $high_rate = $this->calculator->calculate_comparison(array_merge($base_params, ['mortgage_rate' => 5.0]));

        $this->assertGreaterThan(
            $high_rate['rental_scenario']['net_annual_income'],
            $low_rate['rental_scenario']['net_annual_income'],
            'FEHLER: mortgage_rate beeinflusst Nettomieteinnahmen nicht'
        );

        // Höherer Zins = höhere Zinskosten
        $this->assertGreaterThan(
            $low_rate['rental_scenario']['mortgage_interest'],
            $high_rate['rental_scenario']['mortgage_interest'],
            'FEHLER: mortgage_rate beeinflusst Zinskosten nicht'
        );
    }

    /**
     * Test: Haltedauer beeinflusst Spekulationssteuer-Hinweis
     */
    public function test_holding_period_affects_speculation_tax_note() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
            'property_value' => 200000,
            'remaining_mortgage' => 0,
            'mortgage_rate' => 3.5,
        ];

        // Unter 10 Jahre = Spekulationssteuer-Hinweis
        $short_hold = $this->calculator->calculate_comparison(array_merge($base_params, ['holding_period_years' => 5]));
        $this->assertNotNull($short_hold['speculation_tax_note'],
            'FEHLER: Bei Haltedauer < 10 Jahre sollte Spekulationssteuer-Hinweis erscheinen');

        // Über 10 Jahre = kein Hinweis
        $long_hold = $this->calculator->calculate_comparison(array_merge($base_params, ['holding_period_years' => 12]));
        $this->assertNull($long_hold['speculation_tax_note'],
            'FEHLER: Bei Haltedauer >= 10 Jahre sollte kein Spekulationssteuer-Hinweis erscheinen');
    }

    /**
     * Test: Erwartete Wertsteigerung beeinflusst Projektion
     */
    public function test_expected_appreciation_affects_projection() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
            'property_value' => 200000,
            'remaining_mortgage' => 0,
            'mortgage_rate' => 3.5,
            'holding_period_years' => 5,
        ];

        $low_appreciation = $this->calculator->calculate_comparison(array_merge($base_params, ['expected_appreciation' => 1.0]));
        $high_appreciation = $this->calculator->calculate_comparison(array_merge($base_params, ['expected_appreciation' => 4.0]));

        // Jahr 10 Projektion vergleichen
        $low_year_10 = $low_appreciation['projection'][9]['property_value'];
        $high_year_10 = $high_appreciation['projection'][9]['property_value'];

        $this->assertGreaterThan($low_year_10, $high_year_10,
            'FEHLER: expected_appreciation beeinflusst Projektion nicht');
    }

    /**
     * Test: Stadt beeinflusst Vervielfältiger (sale_factor)
     */
    public function test_city_affects_vervielfaeltiger() {
        // Zweite Stadt mit höherem Vervielfältiger
        $matrix = get_option('irp_price_matrix');
        $matrix['cities'][] = [
            'id' => 'premium_city',
            'name' => 'Premium Stadt',
            'base_price' => 15.00,
            'size_degression' => 0.20,
            'sale_factor' => 30,  // Höher als test_city (25)
        ];
        update_option('irp_price_matrix', $matrix);

        $calculator = new IRP_Calculator();

        $base_params = [
            'size' => 70,
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
            'property_value' => 200000,
            'remaining_mortgage' => 0,
            'mortgage_rate' => 3.5,
            'holding_period_years' => 5,
        ];

        $normal = $calculator->calculate_comparison(array_merge($base_params, ['city_id' => 'test_city']));
        $premium = $calculator->calculate_comparison(array_merge($base_params, ['city_id' => 'premium_city']));

        $this->assertEquals(25, $normal['vervielfaeltiger']['factor']);
        $this->assertEquals(30, $premium['vervielfaeltiger']['factor']);

        $this->assertGreaterThan(
            $normal['vervielfaeltiger']['estimated_sale_price'],
            $premium['vervielfaeltiger']['estimated_sale_price'],
            'FEHLER: city_id beeinflusst Vervielfältiger nicht'
        );
    }

    /**
     * Test: Renditeberechnung ist korrekt
     */
    public function test_yield_calculation_is_correct() {
        $result = $this->calculator->calculate_comparison([
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
            'property_value' => 200000,
            'remaining_mortgage' => 0,
            'mortgage_rate' => 0,
            'holding_period_years' => 5,
        ]);

        // Bruttorendite = Jahresmiete / Immobilienwert * 100
        $expected_gross_yield = ($result['rental_scenario']['gross_annual_rent'] / 200000) * 100;
        $this->assertEqualsWithDelta($expected_gross_yield, $result['yields']['gross'], 0.01,
            'FEHLER: Bruttorendite wird nicht korrekt berechnet');
    }

    /**
     * Test: Parameter "rooms" ist Lead-Data-Only (wie bei Mietwert)
     *
     * @see test-rental-calculator.php::test_rooms_is_lead_data_only
     */
    public function test_rooms_is_lead_data_only() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
            'property_value' => 200000,
            'remaining_mortgage' => 0,
            'mortgage_rate' => 3.5,
            'holding_period_years' => 5,
        ];

        $with_2_rooms = $this->calculator->calculate_comparison(array_merge($base_params, ['rooms' => 2]));
        $with_5_rooms = $this->calculator->calculate_comparison(array_merge($base_params, ['rooms' => 5]));

        // Mietberechnung muss identisch sein
        $this->assertEquals(
            $with_2_rooms['rental']['monthly_rent']['estimate'],
            $with_5_rooms['rental']['monthly_rent']['estimate'],
            'Parameter "rooms" sollte keinen Einfluss auf die Berechnung haben (Lead data only)'
        );
    }
}
