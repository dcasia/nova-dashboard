<template>

    <modal @modal-close="handleClose">

        <form @submit.prevent="handleConfirm"
              slot-scope="props"
              class="bg-white rounded-lg shadow-lg overflow-hidden"
              style="width: 800px;">

            <div class="p-8 pb-0">
                <heading :level="2" class="mb-6">{{ __('Widget Options') }}</heading>
            </div>

            <default-field v-if="!editWidget" :field="selectInput" :errors="errors">

                <template slot="field">

                    <select-control
                        :id="selectInput.attribute"
                        v-model="key"
                        class="w-full form-control form-select"
                        :class="errorClasses"
                        :options="selectInput.options"
                        @input.native="onWidgetSelected">

                        <option value="" selected disabled>{{ __('Choose an option') }}</option>

                    </select-control>

                </template>

            </default-field>

            <div v-for="field in fields">

                <component :is="'form-' + field.component"
                           :resource-name="resourceName"
                           :field="field"
                           :errors="validationErrors"/>

            </div>

            <div class="bg-30 px-6 py-3 flex">

                <div class="ml-auto">

                    <button type="button"
                            @click.prevent="handleClose"
                            class="btn text-80 font-normal h-9 px-3 mr-3 btn-link">

                        {{ __('Cancel') }}

                    </button>

                    <button ref="confirmButton"
                            id="confirm-restore-button"
                            data-testid="confirm-button"
                            type="submit"
                            class="btn btn-default btn-primary">

                        {{ editWidget ? __('Update') : __('Create') }}

                    </button>
                </div>

            </div>

        </form>

    </modal>

</template>

<script>

    export default {
        props: [ 'widgets', 'editWidget' ],
        data() {

            return {
                key: this.editWidget ? this.editWidget.key : null,
                selectInput: {
                    attribute: 'widget',
                    component: 'select-field',
                    helpText: null,
                    indexName: 'Widget',
                    name: 'Widget',
                    nullable: false,
                    panel: null,
                    prefixComponent: true,
                    readonly: false,
                    required: true,
                    sortable: false,
                    sortableUriKey: 'widget',
                    stacked: false,
                    textAlign: 'left',
                    validationKey: 'widget',
                    value: null,
                    searchable: false,
                    options: this.widgets.map(widget => {

                        return {
                            label: widget.text,
                            value: widget.key
                        }

                    })
                }
            }

        },
        computed: {
            selectedWidget() {

                return this.widgets.find(widget => widget.key === this.key)

            },
            fields() {

                if (this.selectedWidget) {

                    /**
                     * If it's edit mode hydrate all the inputs with the options of available on the existing widget
                     */
                    if (this.editWidget) {

                        const options = this.editWidget.options || {}

                        for (const attribute in options) {

                            const option = this.selectedWidget.options.find(option => option.attribute === attribute)

                            if (option) {

                                option.value = options[ attribute ]

                            }

                        }

                    }

                    return this.selectedWidget.options || []

                }

                return []

            }
        },
        methods: {
            handleClose() {
                this.$emit('close')
            },
            onWidgetSelected() {

                this.selectedWidget = this.value

            },
            handleConfirm() {

                const formData = new FormData()

                for (const field of this.fields) {

                    field.fill(formData)

                }

                const options = {}

                formData.forEach((value, key) => ( options[ key ] = value ))

                if (this.editWidget) {

                    this.$emit('update', this.editWidget, options)

                } else {

                    this.$emit('create', this.selectedWidget, options)

                }

            }
        }
    }

</script>
