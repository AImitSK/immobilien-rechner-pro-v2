<?php
/**
 * Calculator logic for rental value and comparison calculations
 *
 * ============================================================================
 * PARAMETER DOCUMENTATION
 * ============================================================================
 *
 * This calculator uses the following parameters for calculations:
 *
 * CALCULATION PARAMETERS (used in calculate_rental_value):
 * - property_type: Type of property (apartment, house, commercial)
 * - size: Property size in sqm
 * - city_id: ID of the city for base price lookup
 * - condition: Property condition (new, renovated, good, needs_renovation)
 * - features: Array of property features (balcony, garage, etc.)
 * - year_built: Construction year for age factor calculation
 * - location_rating: Location quality rating (1-5)
 *
 * LEAD DATA ONLY (accepted by API but NOT used in calculation):
 * - rooms: Number of rooms - stored for lead/consultation context
 * - zip_code: Postal code - stored for lead/consultation context
 * - location: Location description - stored for lead/consultation context
 * - address: Full address - stored for lead/consultation context
 *
 * These lead-only parameters are passed through the API for storage with
 * the lead record, enabling brokers to have complete property information
 * for follow-up consultations.
 *
 * ============================================================================
 * SHARED CODE NOTE
 * ============================================================================
 *
 * The get_default_location_ratings() method is duplicated in:
 * - IRP_Calculator (this class)
 * - IRP_Sale_Calculator (class-sale-calculator.php)
 *
 * Both return identical location rating configurations. If updating these
 * values, ensure both classes are updated for consistency. A future
 * refactoring could extract this to a shared utility class or trait.
 *
 * ============================================================================
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_Calculator {

    private array $matrix;

    private array $default_condition_multipliers = [
        'new' => 1.25,
        'renovated' => 1.10,
        'good' => 1.00,
        'needs_renovation' => 0.80,
    ];

    private array $default_type_multipliers = [
        'apartment' => 1.00,
        'house' => 1.15,
        'commercial' => 0.85,
    ];

    private array $default_feature_premiums = [
        'balcony' => 0.50,
        'terrace' => 0.75,
        'garden' => 1.00,
        'elevator' => 0.30,
        'parking' => 0.40,
        'garage' => 0.60,
        'cellar' => 0.20,
        'fitted_kitchen' => 0.50,
        'floor_heating' => 0.40,
        'guest_toilet' => 0.25,
        'barrier_free' => 0.30,
    ];

    private array $default_age_multipliers = [
        'before_1946' => ['name' => 'Altbau (bis 1945)', 'multiplier' => 1.05, 'min_year' => null, 'max_year' => 1945],
        '1946_1959' => ['name' => 'Nachkriegsbau (1946-1959)', 'multiplier' => 0.95, 'min_year' => 1946, 'max_year' => 1959],
        '1960_1979' => ['name' => '60er/70er Jahre (1960-1979)', 'multiplier' => 0.90, 'min_year' => 1960, 'max_year' => 1979],
        '1980_1989' => ['name' => '80er Jahre (1980-1989)', 'multiplier' => 0.95, 'min_year' => 1980, 'max_year' => 1989],
        '1990_1999' => ['name' => '90er Jahre (1990-1999)', 'multiplier' => 1.00, 'min_year' => 1990, 'max_year' => 1999],
        '2000_2014' => ['name' => '2000er Jahre (2000-2014)', 'multiplier' => 1.05, 'min_year' => 2000, 'max_year' => 2014],
        'from_2015' => ['name' => 'Neubau (ab 2015)', 'multiplier' => 1.10, 'min_year' => 2015, 'max_year' => null],
    ];

    public function __construct() {
        $this->load_matrix();
    }

    /**
     * Load price matrix from database or use defaults
     */
    private function load_matrix(): void {
        $saved_matrix = get_option('irp_price_matrix', []);

        $this->matrix = [
            'cities' => !empty($saved_matrix['cities'])
                ? $saved_matrix['cities']
                : [],
            'condition_multipliers' => !empty($saved_matrix['condition_multipliers'])
                ? $saved_matrix['condition_multipliers']
                : $this->default_condition_multipliers,
            'type_multipliers' => !empty($saved_matrix['type_multipliers'])
                ? $saved_matrix['type_multipliers']
                : $this->default_type_multipliers,
            'feature_premiums' => !empty($saved_matrix['feature_premiums'])
                ? $saved_matrix['feature_premiums']
                : $this->default_feature_premiums,
            'interest_rate' => (float) ($saved_matrix['interest_rate'] ?? 3.0),
            'appreciation_rate' => (float) ($saved_matrix['appreciation_rate'] ?? 2.0),
            'rent_increase_rate' => (float) ($saved_matrix['rent_increase_rate'] ?? 2.0),
            'location_ratings' => $saved_matrix['location_ratings'] ?? $this->get_default_location_ratings(),
            'age_multipliers' => !empty($saved_matrix['age_multipliers'])
                ? $saved_matrix['age_multipliers']
                : $this->default_age_multipliers,
        ];
    }

    /**
     * Get default location ratings
     */
    private function get_default_location_ratings(): array {
        return [
            1 => ['name' => 'Einfache Lage', 'multiplier' => 0.85],
            2 => ['name' => 'Normale Lage', 'multiplier' => 0.95],
            3 => ['name' => 'Gute Lage', 'multiplier' => 1.00],
            4 => ['name' => 'Sehr gute Lage', 'multiplier' => 1.10],
            5 => ['name' => 'Premium-Lage', 'multiplier' => 1.25],
        ];
    }

    /**
     * Get location rating data
     */
    public function get_location_rating(int $rating): array {
        $ratings = $this->matrix['location_ratings'];
        return $ratings[$rating] ?? $ratings[3]; // Default to "Gute Lage"
    }

    /**
     * Get all configured cities
     */
    public function get_cities(): array {
        return $this->matrix['cities'];
    }

    /**
     * Get a city by its ID
     */
    public function get_city_by_id(string $city_id): ?array {
        foreach ($this->matrix['cities'] as $city) {
            if ($city['id'] === $city_id) {
                return $city;
            }
        }
        return null;
    }

    /**
     * Get condition multipliers
     */
    private function get_condition_multipliers(): array {
        return $this->matrix['condition_multipliers'];
    }

    /**
     * Get type multipliers
     */
    private function get_type_multipliers(): array {
        return $this->matrix['type_multipliers'];
    }

    /**
     * Get feature premiums
     */
    private function get_feature_premiums(): array {
        return $this->matrix['feature_premiums'];
    }

    /**
     * Get age multipliers
     */
    public function get_age_multipliers(): array {
        return $this->matrix['age_multipliers'];
    }

    /**
     * Get age multiplier for a specific year
     */
    public function get_age_multiplier_for_year(?int $year_built): array {
        $age_multipliers = $this->get_age_multipliers();

        if ($year_built === null) {
            // Default to 90er Jahre (neutral) if no year provided
            return [
                'key' => '1990_1999',
                'name' => $age_multipliers['1990_1999']['name'] ?? '90er Jahre (1990-1999)',
                'multiplier' => (float) ($age_multipliers['1990_1999']['multiplier'] ?? 1.00),
            ];
        }

        foreach ($age_multipliers as $key => $data) {
            $min_year = $data['min_year'] ?? null;
            $max_year = $data['max_year'] ?? null;

            // Check if year falls within this range
            $above_min = ($min_year === null) || ($year_built >= $min_year);
            $below_max = ($max_year === null) || ($year_built <= $max_year);

            if ($above_min && $below_max) {
                return [
                    'key' => $key,
                    'name' => $data['name'] ?? $key,
                    'multiplier' => (float) ($data['multiplier'] ?? 1.00),
                ];
            }
        }

        // Fallback to neutral if no match found
        return [
            'key' => 'unknown',
            'name' => __('Unbekannt', 'immobilien-rechner-pro'),
            'multiplier' => 1.00,
        ];
    }

    private const REFERENCE_SIZE = 70.0; // Reference apartment size in m²

    /**
     * Calculate rental value estimate
     */
    public function calculate_rental_value(array $params): array {
        $size = (float) $params['size'];
        $city_id = $params['city_id'] ?? null;
        $condition = $params['condition'];
        $property_type = $params['property_type'];
        $features = $params['features'] ?? [];
        $year_built = $params['year_built'] ?? null;
        $rooms = $params['rooms'] ?? null;
        $location_rating = (int) ($params['location_rating'] ?? 3); // Default: "Gute Lage"

        // Ensure location_rating is between 1 and 5
        $location_rating = max(1, min(5, $location_rating));

        // Get city data
        $city = null;
        if ($city_id) {
            $city = $this->get_city_by_id($city_id);
        }

        // Fallback to first city if no city_id provided or not found
        if (!$city && !empty($this->matrix['cities'])) {
            $city = $this->matrix['cities'][0];
        }

        // Default values if no city configured
        $base_price = $city ? (float) $city['base_price'] : 12.00;
        $size_degression = $city ? (float) ($city['size_degression'] ?? 0.20) : 0.20;
        $city_name = $city ? $city['name'] : __('Unbekannt', 'immobilien-rechner-pro');

        // Get matrix data
        $condition_multipliers = $this->get_condition_multipliers();
        $type_multipliers = $this->get_type_multipliers();
        $feature_premiums = $this->get_feature_premiums();

        // Start with base price
        $price_per_sqm = $base_price;

        // Apply size degression formula: price = base × (reference / size)^alpha
        // This creates a smooth curve where larger apartments have lower price/m²
        // and smaller apartments have higher price/m²
        if ($size > 0 && $size_degression > 0) {
            $size_factor = pow(self::REFERENCE_SIZE / $size, $size_degression);
            $price_per_sqm *= $size_factor;
        }

        // Apply location rating multiplier
        $location_data = $this->get_location_rating($location_rating);
        $location_multiplier = (float) ($location_data['multiplier'] ?? 1.00);
        $price_per_sqm *= $location_multiplier;

        // Apply condition multiplier
        $price_per_sqm *= $condition_multipliers[$condition] ?? 1.00;

        // Apply property type multiplier
        $price_per_sqm *= $type_multipliers[$property_type] ?? 1.00;

        // Apply feature premiums (added after multipliers)
        foreach ($features as $feature) {
            if (isset($feature_premiums[$feature])) {
                $price_per_sqm += $feature_premiums[$feature];
            }
        }

        // Age adjustment based on configurable age multipliers
        $age_data = $this->get_age_multiplier_for_year($year_built);
        $age_multiplier = $age_data['multiplier'];
        $price_per_sqm *= $age_multiplier;

        // Calculate monthly rent
        $monthly_rent = $size * $price_per_sqm;

        // Calculate range (±15%)
        $rent_low = $monthly_rent * 0.85;
        $rent_high = $monthly_rent * 1.15;

        // Annual calculations
        $annual_rent = $monthly_rent * 12;

        // Market comparison
        $market_position = $this->calculate_market_position($price_per_sqm, $base_price);

        return [
            'monthly_rent' => [
                'estimate' => round($monthly_rent, 2),
                'low' => round($rent_low, 2),
                'high' => round($rent_high, 2),
            ],
            'annual_rent' => round($annual_rent, 2),
            'price_per_sqm' => round($price_per_sqm, 2),
            'market_position' => $market_position,
            'city' => [
                'id' => $city ? $city['id'] : null,
                'name' => $city_name,
            ],
            'factors' => [
                'base_price' => $base_price,
                'size_degression' => $size_degression,
                'size_factor' => $size_degression > 0 ? round(pow(self::REFERENCE_SIZE / $size, $size_degression), 3) : 1.0,
                'reference_size' => self::REFERENCE_SIZE,
                'location_rating' => $location_rating,
                'location_name' => $location_data['name'] ?? '',
                'location_impact' => $location_multiplier,
                'condition_impact' => $condition_multipliers[$condition] ?? 1.00,
                'type_impact' => $type_multipliers[$property_type] ?? 1.00,
                'age_class' => $age_data['key'],
                'age_class_name' => $age_data['name'],
                'age_impact' => $age_multiplier,
                'features_count' => count($features),
            ],
            'calculation_date' => current_time('mysql'),
        ];
    }

    /**
     * Calculate sell vs rent comparison
     */
    public function calculate_comparison(array $params): array {
        // First, get the rental calculation
        $rental = $this->calculate_rental_value($params);

        $property_value = (float) $params['property_value'];
        $remaining_mortgage = (float) ($params['remaining_mortgage'] ?? 0);
        $mortgage_rate = (float) ($params['mortgage_rate'] ?? 3.5) / 100;
        $holding_period = (int) ($params['holding_period_years'] ?? 0);
        $city_id = $params['city_id'] ?? null;

        // Use matrix values for appreciation rate (can be overridden by user)
        $appreciation_rate = isset($params['expected_appreciation'])
            ? (float) $params['expected_appreciation'] / 100
            : $this->matrix['appreciation_rate'] / 100;

        // Get rent increase rate from matrix
        $rent_increase_rate = $this->matrix['rent_increase_rate'] / 100;

        // Get sale factor (Vervielfältiger) for city
        $city = $city_id ? $this->get_city_by_id($city_id) : null;
        if (!$city && !empty($this->matrix['cities'])) {
            $city = $this->matrix['cities'][0];
        }
        $vervielfaeltiger = $city ? (float) $city['sale_factor'] : 25;

        $maintenance_rate = (float) ($this->matrix['maintenance_rate'] ?? 1.5) / 100;
        $vacancy_rate = (float) ($this->matrix['vacancy_rate'] ?? 3) / 100;
        $broker_commission = (float) ($this->matrix['broker_commission'] ?? 3.57) / 100;

        // Calculate annual rental income (after costs)
        $gross_annual_rent = $rental['annual_rent'];
        $vacancy_loss = $gross_annual_rent * $vacancy_rate;
        $maintenance_cost = $property_value * $maintenance_rate;
        $net_annual_rent = $gross_annual_rent - $vacancy_loss - $maintenance_cost;

        // Mortgage costs (if applicable)
        $annual_mortgage_interest = $remaining_mortgage * $mortgage_rate;
        $net_annual_income = $net_annual_rent - $annual_mortgage_interest;

        // Calculate rental yield
        $gross_yield = ($gross_annual_rent / $property_value) * 100;
        $net_yield = ($net_annual_income / $property_value) * 100;

        // Sale scenario
        $sale_costs = $property_value * $broker_commission;
        $net_sale_proceeds = $property_value - $remaining_mortgage - $sale_costs;

        // Speculation tax consideration (simplified)
        $speculation_tax_applies = $holding_period < 10;
        $speculation_tax_note = $speculation_tax_applies
            ? __('Hinweis: Bei Immobilien, die weniger als 10 Jahre gehalten werden, kann Spekulationssteuer anfallen.', 'immobilien-rechner-pro')
            : null;

        // Break-even calculation (years until rental income exceeds sale proceeds)
        $years_projection = [];
        $cumulative_rental = 0;
        $break_even_year = null;

        for ($year = 1; $year <= 30; $year++) {
            // Property appreciates
            $future_value = $property_value * pow(1 + $appreciation_rate, $year);

            // Rental income with configured annual increase
            $year_rental = $net_annual_income * pow(1 + $rent_increase_rate, $year - 1);
            $cumulative_rental += $year_rental;

            // Future sale scenario
            $future_sale_costs = $future_value * $broker_commission;
            $future_mortgage = max(0, $remaining_mortgage - ($year * $remaining_mortgage / 25)); // Simplified paydown
            $future_net_sale = $future_value - $future_mortgage - $future_sale_costs;

            // Total value if keeping (cumulative rent + current value - mortgage)
            $keep_value = $cumulative_rental + $future_value - $future_mortgage;

            $years_projection[] = [
                'year' => $year,
                'property_value' => round($future_value, 2),
                'cumulative_rental_income' => round($cumulative_rental, 2),
                'net_sale_proceeds' => round($future_net_sale, 2),
                'keep_total_value' => round($keep_value, 2),
            ];

            // Find break-even point
            if ($break_even_year === null && $cumulative_rental >= $net_sale_proceeds) {
                $break_even_year = $year;
            }
        }

        // Recommendation logic
        $recommendation = $this->generate_recommendation(
            $net_yield,
            $break_even_year,
            $speculation_tax_applies,
            $net_sale_proceeds
        );

        // Calculate estimated sale price based on Vervielfältiger (if not provided by user)
        $estimated_sale_price = $gross_annual_rent * $vervielfaeltiger;

        return [
            'rental' => $rental,
            'sale' => [
                'property_value' => $property_value,
                'sale_costs' => round($sale_costs, 2),
                'remaining_mortgage' => $remaining_mortgage,
                'net_proceeds' => round($net_sale_proceeds, 2),
            ],
            'rental_scenario' => [
                'gross_annual_rent' => round($gross_annual_rent, 2),
                'vacancy_loss' => round($vacancy_loss, 2),
                'maintenance_cost' => round($maintenance_cost, 2),
                'mortgage_interest' => round($annual_mortgage_interest, 2),
                'net_annual_income' => round($net_annual_income, 2),
            ],
            'yields' => [
                'gross' => round($gross_yield, 2),
                'net' => round($net_yield, 2),
            ],
            'vervielfaeltiger' => [
                'factor' => $vervielfaeltiger,
                'estimated_sale_price' => round($estimated_sale_price, 2),
                'years_to_recover' => round($property_value / $gross_annual_rent, 1),
            ],
            'break_even_year' => $break_even_year,
            'speculation_tax_note' => $speculation_tax_note,
            'projection' => array_slice($years_projection, 0, 15), // First 15 years for chart
            'recommendation' => $recommendation,
            'calculation_date' => current_time('mysql'),
        ];
    }

    /**
     * Calculate where the rental price falls in the market
     */
    private function calculate_market_position(float $price_per_sqm, float $base_price): array {
        // Simplified percentile calculation
        $ratio = $price_per_sqm / $base_price;

        if ($ratio < 0.85) {
            $percentile = 20;
            $label = __('Unterdurchschnittlich', 'immobilien-rechner-pro');
        } elseif ($ratio < 0.95) {
            $percentile = 35;
            $label = __('Leicht unterdurchschnittlich', 'immobilien-rechner-pro');
        } elseif ($ratio < 1.05) {
            $percentile = 50;
            $label = __('Durchschnittlich', 'immobilien-rechner-pro');
        } elseif ($ratio < 1.15) {
            $percentile = 65;
            $label = __('Überdurchschnittlich', 'immobilien-rechner-pro');
        } elseif ($ratio < 1.25) {
            $percentile = 80;
            $label = __('Deutlich überdurchschnittlich', 'immobilien-rechner-pro');
        } else {
            $percentile = 90;
            $label = __('Premium-Segment', 'immobilien-rechner-pro');
        }

        return [
            'percentile' => $percentile,
            'label' => $label,
        ];
    }

    /**
     * Generate a recommendation based on the analysis
     */
    private function generate_recommendation(
        float $net_yield,
        ?int $break_even_year,
        bool $speculation_tax,
        float $net_sale_proceeds
    ): array {
        $factors = [];
        $score = 0; // Positive = favor rent, Negative = favor sell

        // Yield analysis
        if ($net_yield >= 5) {
            $factors[] = __('Die hohe Mietrendite spricht für eine Vermietung.', 'immobilien-rechner-pro');
            $score += 2;
        } elseif ($net_yield >= 3) {
            $factors[] = __('Moderate Mietrendite – berücksichtigen Sie Ihre langfristigen Ziele.', 'immobilien-rechner-pro');
            $score += 1;
        } else {
            $factors[] = __('Die niedrige Mietrendite könnte einen Verkauf attraktiver machen.', 'immobilien-rechner-pro');
            $score -= 1;
        }

        // Break-even analysis
        if ($break_even_year !== null) {
            if ($break_even_year <= 5) {
                $factors[] = sprintf(
                    __('Schneller Break-Even nach %d Jahren spricht für Vermietung.', 'immobilien-rechner-pro'),
                    $break_even_year
                );
                $score += 2;
            } elseif ($break_even_year <= 10) {
                $factors[] = sprintf(
                    __('Moderater Break-Even-Zeitraum von %d Jahren.', 'immobilien-rechner-pro'),
                    $break_even_year
                );
                $score += 1;
            } else {
                $factors[] = sprintf(
                    __('Langer Break-Even-Zeitraum von %d Jahren könnte für Verkauf sprechen.', 'immobilien-rechner-pro'),
                    $break_even_year
                );
                $score -= 1;
            }
        }

        // Tax consideration
        if ($speculation_tax) {
            $factors[] = __('Ein Verkauf jetzt könnte Spekulationssteuer auslösen – erwägen Sie zu warten oder zu vermieten.', 'immobilien-rechner-pro');
            $score += 1;
        }

        // Generate summary
        if ($score >= 2) {
            $summary = __('Basierend auf unserer Analyse erscheint Vermieten als die günstigere Option.', 'immobilien-rechner-pro');
            $direction = 'rent';
        } elseif ($score <= -1) {
            $summary = __('Basierend auf unserer Analyse könnte ein Verkauf für Ihre Situation besser sein.', 'immobilien-rechner-pro');
            $direction = 'sell';
        } else {
            $summary = __('Beide Optionen haben ihre Vorteile. Eine Beratung kann den besten Weg aufzeigen.', 'immobilien-rechner-pro');
            $direction = 'neutral';
        }

        return [
            'direction' => $direction,
            'summary' => $summary,
            'factors' => $factors,
        ];
    }
}
