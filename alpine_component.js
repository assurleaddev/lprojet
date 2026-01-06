// Alpine.js Searchable Select Component
function searchableSelect(config) {
    return {
        open: false,
        search: '',
        selected: config.value || '',
        selectedLabel: config.label || '',
        options: config.options || [],

        get filteredOptions() {
            if (!this.search) return this.options;
            return this.options.filter(opt =>
                opt.label.toLowerCase().includes(this.search.toLowerCase())
            );
        },

        select(value, label) {
            this.selected = value;
            this.selectedLabel = label;
            this.open = false;
            this.search = '';
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.$refs.searchInput.focus());
            }
        },

        close() {
            this.open = false;
        }
    };
}
