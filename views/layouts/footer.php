</main>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Pickr JS (Color Picker) -->
<script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>

<script>
    // Legacy support function just in case some old JS calls it, though we use Bootstrap toggles now
    function toggleSubmenu(id) {
        var el = document.getElementById(id);
        if(el && typeof bootstrap !== 'undefined') {
            // If it's a bootstrap collapse
            var bsCollapse = new bootstrap.Collapse(el, {toggle: true});
        } else if (el) {
            el.classList.toggle('open');
        }
    }

    /**
     * Auto-Initialize Pickr on all input[type="color"]
     * Hides the native input and syncs values.
     */
    document.addEventListener("DOMContentLoaded", function() {
        const colorInputs = document.querySelectorAll('input[type="color"]');

        colorInputs.forEach(input => {
            // Create a wrapper for the picker
            const wrapper = document.createElement('div');
            wrapper.style.marginBottom = '10px';
            input.parentNode.insertBefore(wrapper, input);

            // Hide the native input but keep it for form submission
            input.style.display = 'none'; // Or type="hidden", but keeping it in DOM as color for fallback

            const pickr = Pickr.create({
                el: wrapper,
                theme: 'classic', // or 'monolith', or 'nano'
                default: input.value || '#000000',
                swatches: [
                    'rgba(244, 67, 54, 1)',
                    'rgba(233, 30, 99, 1)',
                    'rgba(156, 39, 176, 1)',
                    'rgba(103, 58, 183, 1)',
                    'rgba(63, 81, 181, 1)',
                    'rgba(33, 150, 243, 1)',
                    'rgba(3, 169, 244, 1)',
                    'rgba(0, 188, 212, 1)',
                    'rgba(0, 150, 136, 1)',
                    'rgba(76, 175, 80, 1)',
                    'rgba(139, 195, 74, 1)',
                    'rgba(205, 220, 57, 1)',
                    'rgba(255, 235, 59, 1)',
                    'rgba(255, 193, 7, 1)'
                ],
                components: {
                    // Main components
                    preview: true,
                    opacity: true,
                    hue: true,

                    // Input / output Options
                    interaction: {
                        hex: true,
                        rgba: true,
                        input: true,
                        clear: false,
                        save: true
                    }
                }
            });

            // On Save/Change, update original input
            pickr.on('save', (color, instance) => {
                const hex = color.toHEXA().toString();
                input.value = hex; // Sync value

                // Trigger change event on original input in case other scripts listen to it
                const event = new Event('change');
                input.dispatchEvent(event);

                pickr.hide();
            });

             pickr.on('change', (color, source, instance) => {
                 // Optional: Real-time sync if needed, but 'save' is safer for 'classic' theme
                 // For now, let's sync on change too for live previews if any
                 const hex = color.toHEXA().toString();
                 // input.value = hex;
             });
        });
    });
</script>
</body>
</html>
