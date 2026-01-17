<?php
/**
 * Sale Value Calculator
 *
 * Implements three valuation methods:
 * - Apartments: Comparative value method (Vergleichswertverfahren)
 * - Houses: Asset value method (Sachwertverfahren)
 * - Land: Pure land value (Bodenwert)
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_Sale_Calculator {

    private array $matrix;
    private array $sale_settings;

    /**
     * Default house type multipliers
     */
    private array $default_house_type_multipliers = [
        'single_family' => ['name' => 'Einfamilienhaus', 'multiplier' => 1.00],
        'multi_family' => ['name' => 'Mehrfamilienhaus', 'multiplier' => 1.15],
        'semi_detached' => ['name' => 'Doppelhaushälfte', 'multiplier' => 0.95],
        'townhouse_middle' => ['name' => 'Mittelreihenhaus', 'multiplier' => 0.88],
        'townhouse_end' => ['name' => 'Endreihenhaus', 'multiplier' => 0.92],
        'bungalow' => ['name' => 'Bungalow', 'multiplier' => 1.05],
    ];

    /**
     * Default quality multipliers
     */
    private array $default_quality_multipliers = [
        'simple' => ['name' => 'Einfach', 'multiplier' => 0.85],
        'normal' => ['name' => 'Normal', 'multiplier' => 1.00],
        'upscale' => ['name' => 'Gehoben', 'multiplier' => 1.15],
        'luxury' => ['name' => 'Luxuriös', 'multiplier' => 1.35],
    ];

    /**
     * Default modernization year shifts (ImmoWertV compliant)
     */
    private array $default_modernization_year_shift = [
        '1-3_years' => ['name' => 'Vor 1-3 Jahren', 'years' => 15],
        '4-9_years' => ['name' => 'Vor 4-9 Jahren', 'years' => 10],
        '10-15_years' => ['name' => 'Vor 10-15 Jahren', 'years' => 5],
        'over_15_years' => ['name' => 'Vor mehr als 15 Jahren', 'years' => 0],
        'never' => ['name' => 'Noch nie', 'years' => 0],
    ];

    /**
     * Default age depreciation settings
     */
    private array $default_age_depreciation = [
        'rate_per_year' => 0.01,
        'max_depreciation' => 0.40,
        'base_year' => 2025,
    ];

    /**
     * Default feature values (absolute amounts in EUR)
     */
    private array $default_features = [
        // Exterior
        'balcony' => ['name' => 'Balkon', 'value' => 5000, 'type' => 'exterior'],
        'garage' => ['name' => 'Garage', 'value' => 15000, 'type' => 'exterior'],
        'parking' => ['name' => 'Stellplatz', 'value' => 8000, 'type' => 'exterior'],
        'garden' => ['name' => 'Garten', 'value' => 8000, 'type' => 'exterior'],
        'terrace' => ['name' => 'Terrasse', 'value' => 6000, 'type' => 'exterior'],
        'solar' => ['name' => 'Solaranlage', 'value' => 12000, 'type' => 'exterior'],
        // Interior
        'elevator' => ['name' => 'Aufzug', 'value' => 20000, 'type' => 'interior'],
        'fitted_kitchen' => ['name' => 'Einbauküche', 'value' => 8000, 'type' => 'interior'],
        'fireplace' => ['name' => 'Kamin', 'value' => 6000, 'type' => 'interior'],
        'parquet' => ['name' => 'Parkettboden', 'value' => 4000, 'type' => 'interior'],
        'cellar' => ['name' => 'Keller', 'value' => 10000, 'type' => 'interior'],
        'attic' => ['name' => 'Dachboden', 'value' => 5000, 'type' => 'interior'],
    ];

    public function __construct() {
        $this->load_settings();
    }

    /**
     * Load settings from database or use defaults
     */
    private function load_settings(): void {
        $saved_matrix = get_option('irp_price_matrix', []);
        $saved_sale_settings = get_option('irp_sale_value_settings', []);

        $this->matrix = [
            'cities' => $saved_matrix['cities'] ?? [],
            'location_ratings' => $saved_matrix['location_ratings'] ?? $this->get_default_location_ratings(),
        ];

        $this->sale_settings = [
            'house_type_multipliers' => $saved_sale_settings['house_type_multipliers'] ?? $this->default_house_type_multipliers,
            'quality_multipliers' => $saved_sale_settings['quality_multipliers'] ?? $this->default_quality_multipliers,
            'modernization_year_shift' => $saved_sale_settings['modernization_year_shift'] ?? $this->default_modernization_year_shift,
            'age_depreciation' => $saved_sale_settings['age_depreciation'] ?? $this->default_age_depreciation,
            'features' => $saved_sale_settings['features'] ?? $this->default_features,
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
     * Main calculation entry point
     */
    public function calculate(array $data): array {
        $city_id = $data['city_id'] ?? null;
        $city_data = $this->get_city_data($city_id);
        $property_type = $data['property_type'] ?? 'house';

        // Route to appropriate calculation method
        return match($property_type) {
            'apartment' => $this->calculate_apartment($data, $city_data),
            'house' => $this->calculate_house($data, $city_data),
            'land' => $this->calculate_land($data, $city_data),
            default => $this->calculate_house($data, $city_data),
        };
    }

    /**
     * Get city data by ID
     */
    private function get_city_data(?string $city_id): array {
        if ($city_id) {
            foreach ($this->matrix['cities'] as $city) {
                if ($city['id'] === $city_id) {
                    return $city;
                }
            }
        }

        // Return first city or defaults
        if (!empty($this->matrix['cities'])) {
            return $this->matrix['cities'][0];
        }

        // Fallback defaults
        return [
            'id' => 'default',
            'name' => __('Standard', 'immobilien-rechner-pro'),
            'land_price_per_sqm' => 150,
            'building_price_per_sqm' => 2500,
            'apartment_price_per_sqm' => 2200,
            'market_adjustment_factor' => 1.00,
        ];
    }

    /**
     * Get location factor
     */
    private function get_location_factor(int $rating): float {
        $ratings = $this->matrix['location_ratings'];
        $rating = max(1, min(5, $rating));
        return (float) ($ratings[$rating]['multiplier'] ?? 1.00);
    }

    /**
     * Apartments: Comparative value method (Vergleichswertverfahren)
     */
    private function calculate_apartment(array $data, array $city): array {
        $living_space = (float) ($data['living_space'] ?? $data['property_size'] ?? 0);
        $build_year = (int) ($data['build_year'] ?? 2000);
        $modernization = $data['modernization'] ?? 'never';
        $quality = $data['quality'] ?? 'normal';
        $location_rating = (int) ($data['location_rating'] ?? 3);
        $features = $data['features'] ?? [];

        // Base: Price per sqm for apartments in this city
        $apartment_price = (float) ($city['apartment_price_per_sqm'] ?? 2200);
        $base_value = $living_space * $apartment_price;

        // Factors
        $quality_factor = $this->get_quality_factor($quality);
        $location_factor = $this->get_location_factor($location_rating);
        $age_factor = $this->calculate_effective_age_factor($build_year, $modernization);
        $market_factor = (float) ($city['market_adjustment_factor'] ?? 1.00);

        // Features value
        $features_value = $this->calculate_features_value($features);

        // Total calculation
        $adjusted_value = $base_value * $quality_factor * $location_factor * $age_factor;
        $total = ($adjusted_value + $features_value) * $market_factor;

        return $this->format_result($total, [
            'calculation_type' => 'apartment',
            'living_space' => $living_space,
            'land_size' => null,
            'features_value' => $features_value,
            'base_price_per_sqm' => $apartment_price,
            'city' => [
                'id' => $city['id'] ?? 'default',
                'name' => $city['name'] ?? __('Standard', 'immobilien-rechner-pro'),
                'average_price_sqm' => $apartment_price,
            ],
            'factors' => [
                'quality' => $quality_factor,
                'quality_name' => $this->get_quality_name($quality),
                'location' => $location_factor,
                'location_rating' => $location_rating,
                'age' => $age_factor,
                'effective_build_year' => $this->calculate_effective_build_year($build_year, $modernization),
                'market' => $market_factor,
            ],
            'breakdown' => [
                'base_value' => round($base_value, 0),
                'after_factors' => round($adjusted_value, 0),
                'features_added' => round($features_value, 0),
                'market_adjusted' => round($total, 0),
            ],
        ]);
    }

    /**
     * Houses: Asset value method (Sachwertverfahren)
     */
    private function calculate_house(array $data, array $city): array {
        $living_space = (float) ($data['living_space'] ?? $data['property_size'] ?? 0);
        $land_size = (float) ($data['land_size'] ?? 0);
        $house_type = $data['house_type'] ?? 'single_family';
        $build_year = (int) ($data['build_year'] ?? 2000);
        $modernization = $data['modernization'] ?? 'never';
        $quality = $data['quality'] ?? 'normal';
        $location_rating = (int) ($data['location_rating'] ?? 3);
        $features = $data['features'] ?? [];

        // Get prices from city config
        $land_price_sqm = (float) ($city['land_price_per_sqm'] ?? 150);
        $building_price_sqm = (float) ($city['building_price_per_sqm'] ?? 2500);
        $market_factor = (float) ($city['market_adjustment_factor'] ?? 1.00);

        // Land value (Bodenwert)
        $land_value = $land_size * $land_price_sqm;

        // Building base value
        $building_base = $living_space * $building_price_sqm;

        // Factors
        $house_type_factor = $this->get_house_type_factor($house_type);
        $quality_factor = $this->get_quality_factor($quality);
        $location_factor = $this->get_location_factor($location_rating);
        $age_factor = $this->calculate_effective_age_factor($build_year, $modernization);

        // Building value with factors (location affects building, not land)
        $building_value = $building_base * $house_type_factor * $quality_factor * $age_factor * $location_factor;

        // Features value
        $features_value = $this->calculate_features_value($features);

        // Asset value (Sachwert) + market adjustment
        $sachwert = $land_value + $building_value + $features_value;
        $total = $sachwert * $market_factor;

        return $this->format_result($total, [
            'calculation_type' => 'house',
            'living_space' => $living_space,
            'land_size' => $land_size,
            'features_value' => $features_value,
            'land_value' => round($land_value, 0),
            'building_value' => round($building_value, 0),
            'city' => [
                'id' => $city['id'] ?? 'default',
                'name' => $city['name'] ?? __('Standard', 'immobilien-rechner-pro'),
                'land_price_sqm' => $land_price_sqm,
                'building_price_sqm' => $building_price_sqm,
            ],
            'factors' => [
                'house_type' => $house_type_factor,
                'house_type_name' => $this->get_house_type_name($house_type),
                'quality' => $quality_factor,
                'quality_name' => $this->get_quality_name($quality),
                'location' => $location_factor,
                'location_rating' => $location_rating,
                'age' => $age_factor,
                'effective_build_year' => $this->calculate_effective_build_year($build_year, $modernization),
                'market' => $market_factor,
            ],
            'breakdown' => [
                'land_value' => round($land_value, 0),
                'building_base' => round($building_base, 0),
                'building_adjusted' => round($building_value, 0),
                'features_added' => round($features_value, 0),
                'sachwert' => round($sachwert, 0),
                'market_adjusted' => round($total, 0),
            ],
        ]);
    }

    /**
     * Land: Pure land value (Bodenwert)
     */
    private function calculate_land(array $data, array $city): array {
        $land_size = (float) ($data['land_size'] ?? 0);
        $location_rating = (int) ($data['location_rating'] ?? 3);

        // Get prices from city config
        $land_price_sqm = (float) ($city['land_price_per_sqm'] ?? 150);
        $market_factor = (float) ($city['market_adjustment_factor'] ?? 1.00);

        // Land value
        $land_value = $land_size * $land_price_sqm;
        $location_factor = $this->get_location_factor($location_rating);

        // Total with location and market adjustment
        $total = $land_value * $location_factor * $market_factor;

        return $this->format_result($total, [
            'calculation_type' => 'land',
            'living_space' => null,
            'land_size' => $land_size,
            'features_value' => 0,
            'land_value' => round($land_value, 0),
            'building_value' => 0,
            'city' => [
                'id' => $city['id'] ?? 'default',
                'name' => $city['name'] ?? __('Standard', 'immobilien-rechner-pro'),
                'land_price_sqm' => $land_price_sqm,
            ],
            'factors' => [
                'location' => $location_factor,
                'location_rating' => $location_rating,
                'market' => $market_factor,
            ],
            'breakdown' => [
                'land_base' => round($land_value, 0),
                'location_adjusted' => round($land_value * $location_factor, 0),
                'market_adjusted' => round($total, 0),
            ],
        ]);
    }

    /**
     * Calculate effective build year considering modernization (ImmoWertV compliant)
     */
    private function calculate_effective_build_year(int $original_year, string $modernization): int {
        $current_year = (int) date('Y');
        $year_shift = $this->sale_settings['modernization_year_shift'][$modernization]['years'] ?? 0;
        $effective_year = $original_year + $year_shift;

        // Cannot be newer than current year
        return min($effective_year, $current_year);
    }

    /**
     * Calculate age factor based on effective build year
     */
    private function calculate_effective_age_factor(int $build_year, string $modernization): float {
        $effective_year = $this->calculate_effective_build_year($build_year, $modernization);
        $settings = $this->sale_settings['age_depreciation'];

        $base_year = (int) ($settings['base_year'] ?? date('Y'));
        $effective_age = $base_year - $effective_year;

        // Calculate depreciation (rate per year, capped at max)
        $rate = (float) ($settings['rate_per_year'] ?? 0.01);
        $max = (float) ($settings['max_depreciation'] ?? 0.40);

        $depreciation = min(max(0, $effective_age) * $rate, $max);

        // Minimum factor is (1 - max_depreciation)
        return max(1 - $max, 1 - $depreciation);
    }

    /**
     * Get house type factor
     */
    private function get_house_type_factor(string $house_type): float {
        return (float) ($this->sale_settings['house_type_multipliers'][$house_type]['multiplier'] ?? 1.00);
    }

    /**
     * Get house type name
     */
    private function get_house_type_name(string $house_type): string {
        return $this->sale_settings['house_type_multipliers'][$house_type]['name']
            ?? $this->default_house_type_multipliers[$house_type]['name']
            ?? $house_type;
    }

    /**
     * Get quality factor
     */
    private function get_quality_factor(string $quality): float {
        return (float) ($this->sale_settings['quality_multipliers'][$quality]['multiplier'] ?? 1.00);
    }

    /**
     * Get quality name
     */
    private function get_quality_name(string $quality): string {
        return $this->sale_settings['quality_multipliers'][$quality]['name']
            ?? $this->default_quality_multipliers[$quality]['name']
            ?? $quality;
    }

    /**
     * Calculate total features value
     */
    private function calculate_features_value(array $selected_features): float {
        $total = 0;
        $features = $this->sale_settings['features'];

        foreach ($selected_features as $feature) {
            if (isset($features[$feature])) {
                $total += (float) ($features[$feature]['value'] ?? 0);
            }
        }

        return $total;
    }

    /**
     * Format the calculation result
     */
    private function format_result(float $total, array $details): array {
        // Round to nearest 1000
        $rounded_total = round($total, -3);

        // Calculate price range (±5%)
        $price_min = round($total * 0.95, -3);
        $price_max = round($total * 1.05, -3);

        $result = [
            'price_estimate' => $rounded_total,
            'price_min' => $price_min,
            'price_max' => $price_max,
            'calculation_type' => $details['calculation_type'],
            'city' => $details['city'],
            'factors' => $details['factors'],
            'breakdown' => $details['breakdown'],
            'features_value' => round($details['features_value'], 0),
            'calculation_date' => current_time('mysql'),
        ];

        // Add price per sqm if applicable
        if (!empty($details['living_space']) && $details['living_space'] > 0) {
            $result['price_per_sqm_living'] = round($total / $details['living_space'], 0);
        }

        if (!empty($details['land_size']) && $details['land_size'] > 0 && isset($details['land_value'])) {
            $result['price_per_sqm_land'] = round($details['land_value'] / $details['land_size'], 0);
            $result['land_value'] = $details['land_value'];
        }

        if (!empty($details['building_value'])) {
            $result['building_value'] = $details['building_value'];
        }

        return $result;
    }

    /**
     * Get all configured cities
     */
    public function get_cities(): array {
        return $this->matrix['cities'];
    }

    /**
     * Get sale settings for admin
     */
    public function get_sale_settings(): array {
        return $this->sale_settings;
    }

    /**
     * Get house type multipliers
     */
    public function get_house_type_multipliers(): array {
        return $this->sale_settings['house_type_multipliers'];
    }

    /**
     * Get quality multipliers
     */
    public function get_quality_multipliers(): array {
        return $this->sale_settings['quality_multipliers'];
    }

    /**
     * Get modernization options
     */
    public function get_modernization_options(): array {
        return $this->sale_settings['modernization_year_shift'];
    }

    /**
     * Get features configuration
     */
    public function get_features(): array {
        return $this->sale_settings['features'];
    }

    /**
     * Get age depreciation settings
     */
    public function get_age_depreciation(): array {
        return $this->sale_settings['age_depreciation'];
    }
}
