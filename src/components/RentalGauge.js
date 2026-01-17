/**
 * Rental Gauge Component
 * Modern radial gauge using ApexCharts
 */

import { useMemo } from '@wordpress/element';
import Chart from 'react-apexcharts';
import { __ } from '@wordpress/i18n';

export default function RentalGauge({ percentile }) {
    // Determine color based on percentile
    const getColor = () => {
        if (percentile < 25) return '#0891b2'; // cyan/info - günstig
        if (percentile < 50) return '#2563eb'; // blue/primary - durchschnittlich
        if (percentile < 75) return '#16a34a'; // green/success - gut
        if (percentile < 90) return '#ea580c'; // orange/warning - hoch
        return '#dc2626'; // red/danger - sehr hoch
    };

    // Get label based on percentile
    const getLabel = () => {
        if (percentile < 25) return __('Günstig', 'immobilien-rechner-pro');
        if (percentile < 50) return __('Durchschnittlich', 'immobilien-rechner-pro');
        if (percentile < 75) return __('Überdurchschnittlich', 'immobilien-rechner-pro');
        if (percentile < 90) return __('Premium', 'immobilien-rechner-pro');
        return __('Luxus', 'immobilien-rechner-pro');
    };

    const options = useMemo(() => ({
        chart: {
            type: 'radialBar',
            offsetY: -10,
            sparkline: {
                enabled: true
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 1000,
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            }
        },
        plotOptions: {
            radialBar: {
                startAngle: -135,
                endAngle: 135,
                hollow: {
                    margin: 0,
                    size: '70%',
                    background: 'transparent',
                },
                track: {
                    background: '#e7e7e7',
                    strokeWidth: '100%',
                    margin: 5,
                    dropShadow: {
                        enabled: true,
                        top: 2,
                        left: 0,
                        color: '#999',
                        opacity: 0.15,
                        blur: 4
                    }
                },
                dataLabels: {
                    name: {
                        offsetY: -10,
                        show: true,
                        color: '#6b7280',
                        fontSize: '14px',
                        fontWeight: 500
                    },
                    value: {
                        formatter: function(val) {
                            return parseInt(val) + '%';
                        },
                        color: getColor(),
                        fontSize: '36px',
                        fontWeight: 700,
                        show: true,
                        offsetY: 5
                    }
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                type: 'horizontal',
                shadeIntensity: 0.5,
                gradientToColors: [getColor()],
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100],
                colorStops: [
                    {
                        offset: 0,
                        color: getColor(),
                        opacity: 0.7
                    },
                    {
                        offset: 100,
                        color: getColor(),
                        opacity: 1
                    }
                ]
            }
        },
        stroke: {
            lineCap: 'round'
        },
        labels: [getLabel()],
        colors: [getColor()]
    }), [percentile]);

    const series = [percentile];

    return (
        <div className="irp-gauge-apex">
            <Chart
                options={options}
                series={series}
                type="radialBar"
                height={280}
            />
            <div className="irp-gauge-scale">
                <span className="irp-gauge-scale-label irp-scale-low">
                    {__('Günstig', 'immobilien-rechner-pro')}
                </span>
                <span className="irp-gauge-scale-label irp-scale-high">
                    {__('Premium', 'immobilien-rechner-pro')}
                </span>
            </div>
        </div>
    );
}
