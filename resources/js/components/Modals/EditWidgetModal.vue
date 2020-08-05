<template>

    <modal @modal-close="handleClose">

        <form @submit.prevent="handleConfirm"
              slot-scope="props"
              class="bg-white rounded-lg shadow-lg overflow-hidden"
              style="width: 800px;">

            <div class="p-8 pb-0">
                <heading :level="2" class="mb-6">{{ __('Update Widget') }}</heading>
            </div>

            <Tabs :headers="headers" ref="tabs">

                <template v-for="tab of schemeTabs" :slot="tab.key" slot-scope="{ show }">

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

                <progress-button @click.native="handleDelete"
                                 class="btn-danger"
                                 :disabled="createWorking || deleteWorking"
                                 :processing="deleteWorking">

                    {{ __('Delete') }}

                </progress-button>

                <div class="ml-auto">

                    <button type="button"
                            @click.prevent="handleClose"
                            :disabled="createWorking || deleteWorking"
                            class="btn text-80 font-normal h-9 px-3 mr-3 btn-link">

                        {{ __('Cancel') }}

                    </button>

                    <progress-button @click.native="handleConfirm"
                                     :disabled="createWorking || deleteWorking"
                                     :processing="createWorking">

                        {{ __('Update') }}

                    </progress-button>

                </div>

            </div>

        </form>

    </modal>

</template>

<script>

import { Errors } from 'laravel-nova'
import Tabs from '../Tabs'
import { dotter } from '../../helpers'

export default {
    name: 'EditWidgetModal',
    components: { Tabs },
    props: {
        dashboardKey: { type: String, required: true },
        viewKey: { type: String, required: true },
        widget: { type: Object, required: true }
    },
    data() {

        return {
            createWorking: false,
            deleteWorking: false,
            errors: new Errors
        }

    },
    computed: {
        namespace() {

            return [ this.dashboardKey, this.viewKey, this.widget.widgetKey, this.widget.id ].join('/')

        },
        headers() {

            return this.schemeTabs.map(({ title, key }) => ( { title, key } ))

        },
        schemeTabs() {

            const tabs = _.cloneDeep(this.widget.schema.tabs || [])
            const options = this.$store.getters[ `${ this.namespace }/options` ]

            for (const tab of tabs) {

                for (const field of tab.fields) {


                    const value = dotter.pick(field.attribute, options)
                    if (value !== undefined) {

                        field.value = value

                    }

                }

            }

            return tabs

        },
        schemeFields() {

            return _.flatten(this.schemeTabs.map(tab => tab.fields))

        }
    },
    methods: {
        updateActiveTab() {

            for (const errorKey in this.errors.all()) {

                for (const tab of this.schemeTabs) {

                    for (const field of tab.fields) {

                        if (field.attribute === errorKey) {

                            this.$refs.tabs.setActiveTab(tab.key)

                        }

                    }

                }

            }

        },
        handleDelete() {

            this.deleteWorking = true

            Nova.request({
                method: 'post',
                url: '/nova-vendor/nova-dashboard/widget/delete',
                data: {
                    id: this.widget.id,
                    dashboard: this.dashboardKey,
                    view: this.viewKey,
                    widget: this.widget.widgetKey
                }
            })
                .then(response => {

                    if (response.data) {

                        this.$emit('deleted', this.widget.id)

                    } else {

                        Nova.error(this.__('There was a problem deleting the widget.'))

                    }

                })
                .catch(error => {

                    this.deleteWorking = false

                    Nova.error(this.__('There was a problem deleting the widget.'))

                })

        },
        handleClose() {

            this.$emit('close')

        },
        handleConfirm() {

            this.createWorking = true

            const formData = new FormData()

            for (const field of this.schemeFields) field.fill(formData)

            Nova.request({
                method: 'post',
                url: '/nova-vendor/nova-dashboard/widget/update',
                params: {
                    id: this.widget.id,
                    dashboard: this.dashboardKey,
                    view: this.viewKey,
                    widget: this.widget.widgetKey,
                    filters: this.$store.getters[ `${ this.dashboardKey }/currentEncodedFilters` ]
                },
                data: formData
            })
                .then(response => {

                    this.handleClose()

                    const data = {}

                    formData.forEach((value, key) => data[ key ] = value)

                    this.$store.commit(`${ this.namespace }/updateOptions`, data)

                    this.$nextTick(() => {

                        this.$emit('updated', response.data)
                        Nova.$emit(`widget-${ response.data }-updated`)

                    })

                })
                .catch(error => {

                    this.createWorking = false

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
