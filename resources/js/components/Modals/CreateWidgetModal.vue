<template>

    <modal @modal-close="handleClose">

        <form @submit.prevent="handleConfirm"
              slot-scope="props"
              class="bg-white rounded-lg shadow-lg overflow-hidden"
              style="width: 800px;">

            <div class="p-8 pb-0">
                <heading :level="2" class="mb-6">{{ __('Create Widget') }}</heading>
            </div>

            <SelectInput id="widget-type"
                         required
                         :label="label"
                         :options="options"
                         :placeholder="placeholder"
                         @input="onWidgetSelected"/>

            <div v-for="field in fields">

                <component :is="'form-' + field.component"
                           :resource-name="dashboardKey"
                           :field="field"
                           :errors="errors"/>

            </div>

            <div class="bg-30 px-6 py-3 flex">

                <div class="ml-auto">

                    <button type="button"
                            @click.prevent="handleClose"
                            class="btn text-80 font-normal h-9 px-3 mr-3 btn-link">

                        {{ __('Cancel') }}

                    </button>

                    <progress-button @click.native="handleConfirm"
                                     :disabled="!selectedSchema || working"
                                     :processing="working">

                        {{ selectedSchema ? __('Create') : __('Select a widget type.') }}

                    </progress-button>

                </div>

            </div>

        </form>

    </modal>

</template>

<script>

    import SelectInput from '../Inputs/SelectInput'
    import { Errors } from 'laravel-nova'

    export default {
        name: 'CreateWidgetModal',
        components: { SelectInput },
        props: {
            dashboardKey: { type: String, required: true },
            viewKey: { type: String, required: true },
            schemas: { type: Object, required: true }
        },
        data() {

            return {
                working: false,
                errors: new Errors,
                label: this.__('Type'),
                placeholder: this.__('Choose an option'),
                selectedSchemaKey: null,
                options: _.keys(this.schemas).map(uriKey => {

                    return {
                        label: this.schemas[ uriKey ].title,
                        value: uriKey
                    }

                })
            }

        },
        computed: {
            selectedSchema() {

                return this.schemas[ this.selectedSchemaKey ]

            },
            fields() {

                if (this.selectedSchema) {

                    return this.selectedSchema.fields || []

                }

                return []

            }
        },
        methods: {
            handleClose() {

                this.$emit('close')

            },
            onWidgetSelected(value) {

                this.selectedSchemaKey = value

            },
            handleConfirm() {

                this.working = true

                const formData = new FormData()

                for (const field of this.fields) field.fill(formData)

                Nova.request({
                    method: 'post',
                    url: '/nova-vendor/nova-dashboard/widget/create',
                    params: {
                        dashboard: this.dashboardKey,
                        view: this.viewKey,
                        widget: this.selectedSchemaKey
                    },
                    data: formData
                })
                    .then(response => {

                        const options = {}

                        /**
                         * Decode every nested json string into an object
                         */
                        formData.forEach((value, key) => {

                            try {

                                options[ key ] = JSON.parse(value)

                            } catch {

                                options[ key ] = value

                            }

                        })

                        this.$emit('created', {
                            id: response.data,
                            schema: this.selectedSchema,
                            coordinates: { x: 0, y: 0, width: 2, height: 1 },
                            widgetKey: this.selectedSchemaKey,
                            dashboardKey: this.dashboardKey,
                            viewKey: this.viewKey,
                            options: options,
                            editable: true,
                            meta: {}
                        })

                        this.handleClose()
                        this.working = false

                    })
                    .catch(error => {

                        this.working = false

                        if (error.response.status === 422) {
                            this.errors = new Errors(error.response.data.errors)
                            Nova.error(this.__('There was a problem creating the widget.'))
                        }

                    })

            }

        }

    }

</script>
