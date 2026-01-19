/**
 * Immobilien Rechner Pro - Admin Scripts
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Logo Upload
        var mediaUploader;

        $('.irp-upload-logo').on('click', function(e) {
            e.preventDefault();

            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media({
                title: irpAdmin.i18n.mediaTitle || 'Logo auswählen',
                button: {
                    text: irpAdmin.i18n.mediaButton || 'Dieses Bild verwenden'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#company_logo').val(attachment.url);

                var preview = $('.irp-logo-preview');
                preview.html('<img src="' + attachment.url + '" alt="Logo">');

                $('.irp-remove-logo').show();

                // Show/hide SVG warning
                var isSvg = attachment.url.toLowerCase().endsWith('.svg') ||
                            (attachment.mime && attachment.mime === 'image/svg+xml');
                if (isSvg) {
                    $('.irp-svg-warning').show();
                } else {
                    $('.irp-svg-warning').hide();
                }
            });

            mediaUploader.open();
        });

        $('.irp-remove-logo').on('click', function(e) {
            e.preventDefault();
            $('#company_logo').val('');
            $('.irp-logo-preview').empty();
            $(this).hide();
            $('.irp-svg-warning').hide();
        });

        // Range slider output update
        $('#calculator_max_width').on('input', function() {
            var value = $(this).val();
            $('output[for="calculator_max_width"]').text(value + 'px');
        });

        // Color picker enhancement
        $('input[type="color"]').each(function() {
            var $input = $(this);
            var $wrapper = $('<div class="irp-color-wrapper"></div>');
            var $preview = $('<span class="irp-color-preview"></span>');
            var $hex = $('<input type="text" class="irp-color-hex small-text" maxlength="7">');

            $input.wrap($wrapper);
            $input.after($hex);
            $hex.val($input.val());

            $input.on('input', function() {
                $hex.val($input.val());
            });

            $hex.on('change', function() {
                var val = $hex.val();
                if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
                    $input.val(val);
                }
            });
        });

        // Matrix: Update Vervielfältiger example calculations
        $('.irp-factor-input').on('input', function() {
            var region = $(this).data('region');
            var factor = parseFloat($(this).val()) || 0;
            var monthlyRent = 1000;
            var price = monthlyRent * 12 * factor;

            $('.irp-calc-price[data-region="' + region + '"]').text(
                price.toLocaleString('de-DE')
            );
        });

        // Matrix: Update multiplier impact display (for condition and type multipliers)
        $('#tab-multipliers .irp-data-table input[type="number"]').on('input', function() {
            var $row = $(this).closest('tr');
            var $impactCell = $row.find('.irp-positive, .irp-negative');

            if ($impactCell.length) {
                var multiplier = parseFloat($(this).val()) || 1;
                var impact = (multiplier - 1) * 100;
                var sign = impact >= 0 ? '+' : '';

                $impactCell
                    .text(sign + Math.round(impact) + '%')
                    .removeClass('irp-positive irp-negative')
                    .addClass(impact >= 0 ? 'irp-positive' : 'irp-negative');
            }
        });

        // Matrix: Update feature premium example (based on 70m² reference)
        $('#irp-features-table .irp-feature-input').on('input', function() {
            var premium = parseFloat($(this).val()) || 0;
            var monthlyExtra = premium * 70; // 70m² reference size
            var $result = $(this).closest('tr').find('.irp-feature-result');

            if ($result.length) {
                $result.text('+' + Math.round(monthlyExtra).toLocaleString('de-DE') + ' €/Monat');
            }
        });

        // Matrix: Update location rating impact display
        $('.irp-location-multiplier').on('input', function() {
            var $row = $(this).closest('tr');
            var $impactCell = $row.find('.irp-location-impact');
            var multiplier = parseFloat($(this).val()) || 1;
            var impact = (multiplier - 1) * 100;
            var sign = impact >= 0 ? '+' : '';

            $impactCell
                .text(sign + Math.round(impact) + '%')
                .removeClass('irp-positive irp-negative')
                .addClass(impact >= 0 ? 'irp-positive' : 'irp-negative');
        });

        // Cities: Add new city row
        $('#irp-add-city').on('click', function() {
            var $tbody = $('#irp-cities-body');
            var $rows = $tbody.find('.irp-city-row');
            var newIndex = 0;

            // Find the highest index and add 1
            $rows.each(function() {
                var idx = parseInt($(this).data('index')) || 0;
                if (idx >= newIndex) {
                    newIndex = idx + 1;
                }
            });

            var newRow = `
                <tr class="irp-city-row" data-index="${newIndex}">
                    <td>
                        <input type="text"
                               name="irp_price_matrix[cities][${newIndex}][id]"
                               value=""
                               class="regular-text irp-city-id"
                               placeholder="z.B. stadt_name"
                               pattern="[a-z0-9_-]+"
                               required>
                    </td>
                    <td>
                        <input type="text"
                               name="irp_price_matrix[cities][${newIndex}][name]"
                               value=""
                               class="regular-text"
                               placeholder="Stadtname"
                               required>
                    </td>
                    <td>
                        <input type="number"
                               name="irp_price_matrix[cities][${newIndex}][base_price]"
                               value="12.00"
                               step="0.10"
                               min="1"
                               max="100"
                               class="small-text"> €/m²
                    </td>
                    <td>
                        <input type="number"
                               name="irp_price_matrix[cities][${newIndex}][size_degression]"
                               value="0.20"
                               step="0.01"
                               min="0"
                               max="0.5"
                               class="small-text">
                    </td>
                    <td>
                        <input type="number"
                               name="irp_price_matrix[cities][${newIndex}][sale_factor]"
                               value="25"
                               step="0.5"
                               min="5"
                               max="60"
                               class="small-text">
                    </td>
                    <td>
                        <button type="button" class="button irp-remove-city" title="Stadt entfernen">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </td>
                </tr>
            `;

            $tbody.append(newRow);
        });

        // Cities: Remove city row
        $(document).on('click', '.irp-remove-city', function() {
            var $tbody = $('#irp-cities-body');
            var $rows = $tbody.find('.irp-city-row');

            // Don't allow removing the last row
            if ($rows.length <= 1) {
                alert('Sie müssen mindestens eine Stadt konfiguriert haben.');
                return;
            }

            $(this).closest('tr').remove();
        });

        // Cities: Auto-generate ID from name
        $(document).on('input', '.irp-cities-table input[name*="[name]"]', function() {
            var $row = $(this).closest('tr');
            var $idInput = $row.find('.irp-city-id');

            // Only auto-fill if ID is empty
            if (!$idInput.val()) {
                var name = $(this).val();
                var id = name
                    .toLowerCase()
                    .replace(/ä/g, 'ae')
                    .replace(/ö/g, 'oe')
                    .replace(/ü/g, 'ue')
                    .replace(/ß/g, 'ss')
                    .replace(/[^a-z0-9]/g, '_')
                    .replace(/_+/g, '_')
                    .replace(/^_|_$/g, '');

                $idInput.val(id);
            }
        });

        // =====================================================
        // Shortcode Generator
        // =====================================================

        if ($('.irp-shortcode-generator-wrap').length) {
            initShortcodeGenerator();
        }

        function initShortcodeGenerator() {
            var $output = $('#irp-generated-shortcode');
            var $copyBtn = $('#irp-copy-shortcode');
            var $copySuccess = $('#irp-copy-success');

            // Info elements
            var $infoMode = $('#irp-info-mode span').last();
            var $infoCity = $('#irp-info-city span').last();
            var $infoTheme = $('#irp-info-theme span').last();
            var $infoBranding = $('#irp-info-branding span').last();

            // Step elements
            var $stepMode = $('#irp-step-mode');
            var $stepCity = $('#irp-step-city');

            // Update shortcode on any change
            $('input[name="irp_mode"], #irp-city-select, #irp-theme, #irp-show-branding').on('change input', function() {
                updateShortcode();
            });

            function updateShortcode() {
                var mode = $('input[name="irp_mode"]:checked').val();
                var cityId = $('#irp-city-select').val();
                var cityName = $('#irp-city-select option:selected').text();
                var theme = $('#irp-theme').val();
                var showBranding = $('#irp-show-branding').is(':checked');

                // Build shortcode
                var shortcode = '[immobilien_rechner';
                var params = [];

                if (mode) {
                    params.push('mode="' + mode + '"');
                }
                if (cityId) {
                    params.push('city_id="' + cityId + '"');
                }
                if (theme !== 'light') {
                    params.push('theme="' + theme + '"');
                }
                if (!showBranding) {
                    params.push('show_branding="false"');
                }

                if (params.length > 0) {
                    shortcode += ' ' + params.join(' ');
                }
                shortcode += ']';

                $output.text(shortcode);

                // Update info panel
                updateInfoPanel(mode, cityId, cityName, theme, showBranding);

                // Update steps preview
                updateStepsPreview(mode, cityId);
            }

            function updateInfoPanel(mode, cityId, cityName, theme, showBranding) {
                // Mode info
                var modeText = 'Benutzer wählt';
                if (mode === 'rental') {
                    modeText = 'Nur Mietwert-Rechner';
                } else if (mode === 'comparison') {
                    modeText = 'Nur Vergleich';
                } else if (mode === 'sale_value') {
                    modeText = 'Nur Verkaufswert';
                }
                $infoMode.text('Modus: ' + modeText);

                // City info
                var cityText = 'Benutzer wählt';
                if (cityId) {
                    cityText = cityName.replace(/\s*\(.*\)/, ''); // Remove ID part
                }
                $infoCity.text('Stadt: ' + cityText);

                // Theme info
                var themeText = theme === 'light' ? 'Hell' : 'Dunkel';
                $infoTheme.text('Theme: ' + themeText);

                // Branding info
                var brandingText = showBranding ? 'Sichtbar' : 'Ausgeblendet';
                $infoBranding.text('Branding: ' + brandingText);
            }

            function updateStepsPreview(mode, cityId) {
                // Mode step
                if (mode) {
                    $stepMode.addClass('irp-step-skipped');
                    $stepMode.find('.irp-step-label').text('Übersprungen');
                } else {
                    $stepMode.removeClass('irp-step-skipped');
                    $stepMode.find('.irp-step-label').text('Modus wählen');
                }

                // City step
                if (cityId) {
                    $stepCity.addClass('irp-step-skipped');
                    $stepCity.find('.irp-step-label').text('Übersprungen');
                } else {
                    $stepCity.removeClass('irp-step-skipped');
                    $stepCity.find('.irp-step-label').text('Stadt wählen');
                }

                // Renumber visible steps
                var stepNum = 1;
                $('.irp-steps-flow .irp-step-item').each(function() {
                    if (!$(this).hasClass('irp-step-skipped')) {
                        $(this).find('.irp-step-number').text(stepNum);
                        stepNum++;
                    } else {
                        $(this).find('.irp-step-number').text('—');
                    }
                });
            }

            // Copy to clipboard
            $copyBtn.on('click', function() {
                var shortcode = $output.text();

                navigator.clipboard.writeText(shortcode).then(function() {
                    // Show success message
                    $copySuccess.addClass('visible');
                    $copyBtn.addClass('copied');

                    setTimeout(function() {
                        $copySuccess.removeClass('visible');
                        $copyBtn.removeClass('copied');
                    }, 2000);
                }).catch(function() {
                    // Fallback for older browsers
                    var $temp = $('<textarea>');
                    $('body').append($temp);
                    $temp.val(shortcode).select();
                    document.execCommand('copy');
                    $temp.remove();

                    $copySuccess.addClass('visible');
                    setTimeout(function() {
                        $copySuccess.removeClass('visible');
                    }, 2000);
                });
            });

            // Initial update
            updateShortcode();
        }

        // =====================================================
        // Test Email
        // =====================================================

        $('#irp-send-test-email').on('click', function() {
            var $btn = $(this);
            var $email = $('#irp-test-email');
            var $result = $('#irp-test-email-result');
            var email = $email.val();

            if (!email || !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                $result.removeClass('notice-success').addClass('notice-error')
                    .html('<p>Bitte geben Sie eine gültige E-Mail-Adresse ein.</p>').show();
                return;
            }

            $btn.prop('disabled', true).text('Sende...');
            $result.hide();

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'irp_send_test_email',
                    nonce: irpAdmin.nonce,
                    email: email
                },
                success: function(response) {
                    if (response.success) {
                        $result.removeClass('notice-error').addClass('notice-success')
                            .html('<p>' + response.data.message + '</p>').show();
                    } else {
                        $result.removeClass('notice-success').addClass('notice-error')
                            .html('<p>' + response.data.message + '</p>').show();
                    }
                },
                error: function() {
                    $result.removeClass('notice-success').addClass('notice-error')
                        .html('<p>Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.</p>').show();
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Test-E-Mail senden');
                }
            });
        });

        // =====================================================
        // City Accordion
        // =====================================================

        // Toggle city accordion
        $(document).on('click', '.irp-city-header', function(e) {
            // Don't toggle if clicking on delete button or input
            if ($(e.target).closest('.irp-city-delete, input, button').length) {
                return;
            }
            $(this).closest('.irp-city-item').toggleClass('open');
        });

        // Open first city by default
        $('.irp-city-accordion .irp-city-item:first').addClass('open');

        // Add new city (accordion version)
        $(document).on('click', '.irp-add-city-btn', function() {
            var $accordion = $('.irp-city-accordion');
            var $items = $accordion.find('.irp-city-item');
            var newIndex = 0;

            // Find highest index
            $items.each(function() {
                var idx = parseInt($(this).data('city-index')) || 0;
                if (idx >= newIndex) {
                    newIndex = idx + 1;
                }
            });

            var newCity = createCityAccordionItem(newIndex);
            $accordion.append(newCity);

            // Close all others and open new one
            $items.removeClass('open');
            $accordion.find('.irp-city-item:last').addClass('open');

            // Focus on name input
            $accordion.find('.irp-city-item:last input[name*="[name]"]').focus();
        });

        // Remove city (accordion version)
        $(document).on('click', '.irp-city-accordion .irp-city-delete', function(e) {
            e.stopPropagation();
            var $accordion = $('.irp-city-accordion');
            var $items = $accordion.find('.irp-city-item');

            if ($items.length <= 1) {
                alert(irpAdmin.i18n.minOneCity || 'Sie müssen mindestens eine Stadt konfiguriert haben.');
                return;
            }

            var $item = $(this).closest('.irp-city-item');
            var cityName = $item.find('.irp-city-title').text();

            if (confirm((irpAdmin.i18n.confirmDeleteCity || 'Stadt "{city}" wirklich löschen?').replace('{city}', cityName))) {
                $item.slideUp(200, function() {
                    $(this).remove();
                    // Open first remaining city if none open
                    if (!$accordion.find('.irp-city-item.open').length) {
                        $accordion.find('.irp-city-item:first').addClass('open');
                    }
                });
            }
        });

        // Auto-update city title in accordion header
        $(document).on('input', '.irp-city-accordion input[name*="[name]"]', function() {
            var $item = $(this).closest('.irp-city-item');
            var name = $(this).val() || (irpAdmin.i18n.newCity || 'Neue Stadt');
            $item.find('.irp-city-title').text(name);
        });

        // Auto-update city ID in accordion header
        $(document).on('input', '.irp-city-accordion input[name*="[id]"]', function() {
            var $item = $(this).closest('.irp-city-item');
            var id = $(this).val() || '...';
            $item.find('.irp-city-header .irp-city-id').text(id);
        });

        // Auto-generate ID from name (accordion version)
        $(document).on('input', '.irp-city-accordion input[name*="[name]"]', function() {
            var $item = $(this).closest('.irp-city-item');
            var $idInput = $item.find('input[name*="[id]"]');

            // Only auto-fill if ID is empty or matches auto-generated pattern
            if (!$idInput.data('manual')) {
                var name = $(this).val();
                var id = name
                    .toLowerCase()
                    .replace(/ä/g, 'ae')
                    .replace(/ö/g, 'oe')
                    .replace(/ü/g, 'ue')
                    .replace(/ß/g, 'ss')
                    .replace(/[^a-z0-9]/g, '_')
                    .replace(/_+/g, '_')
                    .replace(/^_|_$/g, '');

                $idInput.val(id).trigger('input');
            }
        });

        // Mark ID as manually edited
        $(document).on('change', '.irp-city-accordion input[name*="[id]"]', function() {
            $(this).data('manual', true);
        });

        // =====================================================
        // Multiplier Slider
        // =====================================================

        // Update multiplier value display and impact
        $(document).on('input', '.irp-multiplier-slider input[type="range"]', function() {
            var $slider = $(this).closest('.irp-multiplier-slider');
            var value = parseFloat($(this).val());
            var $valueDisplay = $slider.find('.irp-multiplier-value');
            var $impact = $slider.find('.irp-impact');

            // Update value display
            $valueDisplay.text(value.toFixed(2));

            // Update hidden input if exists
            var $hidden = $slider.find('input[type="hidden"]');
            if ($hidden.length) {
                $hidden.val(value);
            }

            // Update impact indicator
            if ($impact.length) {
                var impact = (value - 1) * 100;
                var sign = impact >= 0 ? '+' : '';
                $impact
                    .text(sign + Math.round(impact) + '%')
                    .removeClass('irp-impact-positive irp-impact-negative irp-impact-neutral')
                    .addClass(impact > 0 ? 'irp-impact-positive' : (impact < 0 ? 'irp-impact-negative' : 'irp-impact-neutral'));
            }
        });

        // =====================================================
        // Number Input Validation (no spinner)
        // =====================================================

        // Allow only numbers and decimal point
        $(document).on('keypress', '.irp-number-input', function(e) {
            var char = String.fromCharCode(e.which);
            var value = $(this).val();

            // Allow: backspace, delete, tab, escape, enter, decimal point, comma
            if (e.which === 8 || e.which === 0 || e.which === 13) {
                return true;
            }

            // Allow decimal point/comma (only one)
            if ((char === '.' || char === ',') && value.indexOf('.') === -1 && value.indexOf(',') === -1) {
                return true;
            }

            // Allow numbers
            if (char >= '0' && char <= '9') {
                return true;
            }

            e.preventDefault();
            return false;
        });

        // Normalize decimal separator on blur
        $(document).on('blur', '.irp-number-input', function() {
            var value = $(this).val().replace(',', '.');
            if (value && !isNaN(parseFloat(value))) {
                $(this).val(parseFloat(value));
            }
        });

        // =====================================================
        // Helper: Create City Accordion Item HTML
        // =====================================================

        function createCityAccordionItem(index) {
            var i18n = irpAdmin.i18n || {};
            return `
                <div class="irp-city-item open" data-city-index="${index}">
                    <div class="irp-city-header">
                        <span class="irp-city-toggle dashicons dashicons-arrow-right-alt2"></span>
                        <span class="irp-city-title">${i18n.newCity || 'Neue Stadt'}</span>
                        <code class="irp-city-id">...</code>
                        <button type="button" class="irp-city-delete" title="${i18n.deleteCity || 'Stadt löschen'}">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    <div class="irp-city-content">
                        <div class="irp-city-group">
                            <div class="irp-city-group-header">
                                <span class="dashicons dashicons-admin-home"></span>
                                <h4>${i18n.baseData || 'Basisdaten'}</h4>
                            </div>
                            <div class="irp-city-field">
                                <span class="irp-city-field-label">
                                    ${i18n.cityId || 'Stadt-ID'}
                                    <span class="irp-tooltip">
                                        <span class="dashicons dashicons-editor-help"></span>
                                        <span class="irp-tooltip-text">${i18n.cityIdTooltip || 'Eindeutige Kennung für den Shortcode (z.B. muenchen)'}</span>
                                    </span>
                                </span>
                                <div class="irp-city-field-input">
                                    <input type="text"
                                           name="irp_price_matrix[cities][${index}][id]"
                                           value=""
                                           class="irp-number-input irp-input-wide"
                                           placeholder="z.B. muenchen"
                                           pattern="[a-z0-9_-]+"
                                           required>
                                </div>
                            </div>
                            <div class="irp-city-field">
                                <span class="irp-city-field-label">${i18n.cityName || 'Name'}</span>
                                <div class="irp-city-field-input">
                                    <input type="text"
                                           name="irp_price_matrix[cities][${index}][name]"
                                           value=""
                                           class="irp-number-input irp-input-wide"
                                           placeholder="${i18n.cityNamePlaceholder || 'Stadtname'}"
                                           required>
                                </div>
                            </div>
                        </div>
                        <div class="irp-city-group">
                            <div class="irp-city-group-header">
                                <span class="dashicons dashicons-building"></span>
                                <h4>${i18n.rentalParams || 'Mietwert-Parameter'}</h4>
                            </div>
                            <div class="irp-city-field">
                                <span class="irp-city-field-label">
                                    ${i18n.basePrice || 'Basis-Mietpreis'}
                                    <span class="irp-tooltip">
                                        <span class="dashicons dashicons-editor-help"></span>
                                        <span class="irp-tooltip-text">${i18n.basePriceTooltip || 'Ausgangspreis für eine 70m² Referenzwohnung in durchschnittlicher Lage und normalem Zustand.'}</span>
                                    </span>
                                </span>
                                <div class="irp-city-field-input">
                                    <div class="irp-input-group">
                                        <input type="text"
                                               name="irp_price_matrix[cities][${index}][base_price]"
                                               value="12.00"
                                               class="irp-number-input"
                                               inputmode="decimal">
                                        <span class="irp-input-suffix">€/m²</span>
                                    </div>
                                </div>
                            </div>
                            <div class="irp-city-field">
                                <span class="irp-city-field-label">
                                    ${i18n.degression || 'Größendegression'}
                                    <span class="irp-tooltip">
                                        <span class="dashicons dashicons-editor-help"></span>
                                        <span class="irp-tooltip-text">${i18n.degressionTooltip || 'Steuert wie stark der m²-Preis bei größeren Wohnungen sinkt. 0.20 = Standard, 0 = keine Anpassung.'}</span>
                                    </span>
                                </span>
                                <div class="irp-city-field-input">
                                    <input type="text"
                                           name="irp_price_matrix[cities][${index}][size_degression]"
                                           value="0.20"
                                           class="irp-number-input irp-input-narrow"
                                           inputmode="decimal">
                                </div>
                            </div>
                            <div class="irp-city-field">
                                <span class="irp-city-field-label">
                                    ${i18n.saleFactor || 'Vervielfältiger'}
                                    <span class="irp-tooltip">
                                        <span class="dashicons dashicons-editor-help"></span>
                                        <span class="irp-tooltip-text">${i18n.saleFactorTooltip || 'Anzahl Jahresnettokaltmieten für den Kaufpreis. Bei 1.000€/Monat und Faktor 25 → 300.000€ Kaufpreis.'}</span>
                                    </span>
                                </span>
                                <div class="irp-city-field-input">
                                    <input type="text"
                                           name="irp_price_matrix[cities][${index}][sale_factor]"
                                           value="25"
                                           class="irp-number-input irp-input-narrow"
                                           inputmode="decimal">
                                </div>
                            </div>
                        </div>
                        <div class="irp-city-group">
                            <div class="irp-city-group-header">
                                <span class="dashicons dashicons-money-alt"></span>
                                <h4>${i18n.saleParams || 'Verkaufswert-Parameter'}</h4>
                            </div>
                            <div class="irp-city-field">
                                <span class="irp-city-field-label">
                                    ${i18n.landPrice || 'Bodenrichtwert'}
                                    <span class="irp-tooltip">
                                        <span class="dashicons dashicons-editor-help"></span>
                                        <span class="irp-tooltip-text">${i18n.landPriceTooltip || 'Durchschnittlicher Grundstückspreis pro m² in dieser Stadt (vom Gutachterausschuss).'}</span>
                                    </span>
                                </span>
                                <div class="irp-city-field-input">
                                    <div class="irp-input-group">
                                        <input type="text"
                                               name="irp_price_matrix[cities][${index}][land_price_per_sqm]"
                                               value="150"
                                               class="irp-number-input"
                                               inputmode="decimal">
                                        <span class="irp-input-suffix">€/m²</span>
                                    </div>
                                </div>
                            </div>
                            <div class="irp-city-field">
                                <span class="irp-city-field-label">
                                    ${i18n.buildingPrice || 'Gebäude'}
                                    <span class="irp-tooltip">
                                        <span class="dashicons dashicons-editor-help"></span>
                                        <span class="irp-tooltip-text">${i18n.buildingPriceTooltip || 'Normalherstellungskosten für Wohngebäude - Neubaukosten pro m² Wohnfläche.'}</span>
                                    </span>
                                </span>
                                <div class="irp-city-field-input">
                                    <div class="irp-input-group">
                                        <input type="text"
                                               name="irp_price_matrix[cities][${index}][building_price_per_sqm]"
                                               value="2500"
                                               class="irp-number-input"
                                               inputmode="decimal">
                                        <span class="irp-input-suffix">€/m²</span>
                                    </div>
                                </div>
                            </div>
                            <div class="irp-city-field">
                                <span class="irp-city-field-label">
                                    ${i18n.apartmentPrice || 'Wohnung'}
                                    <span class="irp-tooltip">
                                        <span class="dashicons dashicons-editor-help"></span>
                                        <span class="irp-tooltip-text">${i18n.apartmentPriceTooltip || 'Durchschnittlicher Verkaufspreis pro m² für Eigentumswohnungen in dieser Stadt.'}</span>
                                    </span>
                                </span>
                                <div class="irp-city-field-input">
                                    <div class="irp-input-group">
                                        <input type="text"
                                               name="irp_price_matrix[cities][${index}][apartment_price_per_sqm]"
                                               value="2200"
                                               class="irp-number-input"
                                               inputmode="decimal">
                                        <span class="irp-input-suffix">€/m²</span>
                                    </div>
                                </div>
                            </div>
                            <div class="irp-city-field">
                                <span class="irp-city-field-label">
                                    ${i18n.marketFactor || 'Marktfaktor'}
                                    <span class="irp-tooltip">
                                        <span class="dashicons dashicons-editor-help"></span>
                                        <span class="irp-tooltip-text">${i18n.marketFactorTooltip || 'Marktanpassung: 0.8 = schwacher Markt, 1.0 = normal, 1.2+ = Boom-Markt.'}</span>
                                    </span>
                                </span>
                                <div class="irp-city-field-input">
                                    <div class="irp-multiplier-slider">
                                        <input type="range"
                                               min="0.5"
                                               max="2.0"
                                               step="0.05"
                                               value="1.00">
                                        <input type="hidden"
                                               name="irp_price_matrix[cities][${index}][market_adjustment_factor]"
                                               value="1.00">
                                        <span class="irp-multiplier-value">1.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

    });

})(jQuery);
