<template>

    <search-input @input="performSearch"
                  @clear="clearSelection"
                  @selected="selectOption"
                  :value="selectedOption"
                  :data="filteredOptions"
                  :clearable="false"
                  trackBy="value"
                  class="w-full view-select">


        <div slot="default" v-if="selectedOption"
             class="view-select__label flex items-center text-90 font-normal text-2xl">

            {{ selectedOption.label }}

        </div>

        <slot v-else name="default">
            <div class="text-70">{{ __('Click to select an view') }}</div>
        </slot>

        <div slot="option"
             slot-scope="{ option, selected }"
             class="flex items-center text-sm font-semibold leading-5 text-90"
             :class="{ 'text-white': selected }">

            {{ option.label }}

        </div>

    </search-input>

</template>

<script>

    export default {
        props: {
            views: { type: Array, default: () => [] },
            selectedView: { type: String, default: null }
        },
        data() {
            return {
                selectedOption: null,
                search: ''
            }
        },
        created() {

            if (this.selectedView) {

                this.selectedOption = this.options.find(option => option.value === this.selectedView)

            }

        },
        methods: {
            selectOption(option) {
                this.selectedOption = option
                this.value = option.value
                this.$emit('change', this.value)
            },
            performSearch(event) {
                this.search = event
            },
            clearSelection() {
                this.selectedOption = ''
                this.value = ''
            }
        },
        computed: {
            options() {
                return this.views.map(view => {
                    return {
                        label: view.title,
                        value: view.uriKey
                    }
                })
            },
            filteredOptions() {
                return this.options.filter(option => {
                    return option.label.toLowerCase().indexOf(this.search.toLowerCase()) > -1
                })
            }
        }
    }

</script>

<style lang="scss">

    .view-select {

        .search-input-trigger {
            display: none;
        }

        .form-input {
            min-width: 300px;
            cursor: pointer;
            height: auto;
        }

        .form-input:active, .form-input:focus {
            background-color: var(--white);
            box-shadow: none;
        }

        .form-select {
            background: transparent;
            border: none;
            padding: 0;
        }

    }

</style>
