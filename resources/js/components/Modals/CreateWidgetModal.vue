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

            <Tabs v-if="selectedSchemaKey" :headers="headers" ref="tabs">

                <template v-for="tab of selectedSchema.tabs" :slot="tab.key" slot-scope="{ show }">

                    <div v-show="show">

                        <component v-for="field in tab.fields"
                                   :key="field.attribute"
                                   :is="'form-' + field.component"
                                   :resource-name="dashboardKey"
                                   :field="field"
                                   :errors="errors"/>

                    </div>

                </template>

            </Tabs>

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
import Tabs from '../Tabs'

export default {
    name: 'CreateWidgetModal',
    components: { SelectInput, Tabs },
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
        tabs() {

            if (this.selectedSchema) {

                return this.selectedSchema.tabs

            }

            return []

        },
        headers() {

            return this.tabs.map(({ title, key }) => ( { title, key } ))

        },
        selectedSchema() {

            return this.schemas[ this.selectedSchemaKey ]

        },
        fields() {

            return _.flatten(this.tabs.map(tab => tab.fields))

        }
    },
    methods: {
        updateActiveTab() {

            for (const errorKey in this.errors.all()) {

                for (const tab of this.selectedSchema.tabs) {

                    for (const field of tab.fields) {

                        if (field.attribute === errorKey) {

                            this.$refs.tabs.setActiveTab(tab.key)

                        }

                    }

                }

            }

        },
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

                    this.$emit('created', {
                        id: response.data.id,
                        schema: this.selectedSchema,
                        coordinates: { x: 0, y: 0, width: 2, height: 1 },
                        widgetKey: this.selectedSchemaKey,
                        dashboardKey: this.dashboardKey,
                        viewKey: this.viewKey,
                        options: response.data.options,
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
                        this.updateActiveTab()
                        Nova.error(this.__('There was a problem creating the widget.'))
                    }

                })

        }

    }

}

</script>
