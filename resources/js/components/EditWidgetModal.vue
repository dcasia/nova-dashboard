<template>

    <modal @modal-close="handleClose">

        <form @submit.prevent="handleConfirm"
              slot-scope="props"
              class="bg-white rounded-lg shadow-lg overflow-hidden"
              style="width: 800px;">

            <div class="p-8 pb-0">
                <heading :level="2" class="mb-6">{{ __('Update Widget') }}</heading>
            </div>

            <div v-for="field in fields">

                <component :is="'form-' + field.component"
                           :resource-name="dashboardKey"
                           :field="field"
                           :errors="errors"/>

            </div>

            <div class="bg-30 px-6 py-3 flex">

                <button type="button"
                        @click.prevent="handleDelete"
                        class="btn btn-default btn-danger">

                    {{ __('Delete') }}

                </button>

                <div class="ml-auto">

                    <button type="button"
                            @click.prevent="handleClose"
                            class="btn text-80 font-normal h-9 px-3 mr-3 btn-link">

                        {{ __('Cancel') }}

                    </button>

                    <button type="submit"
                            class="btn btn-default btn-primary">

                        {{ __('Update') }}

                    </button>

                </div>

            </div>

        </form>

    </modal>

</template>

<script>

    import { Errors } from 'laravel-nova'

    export default {
        props: {
            dashboardKey: { type: String, required: true },
            viewKey: { type: String, required: true },
            widget: { type: Object }
        },
        data() {

            return {
                errors: new Errors
            }

        },
        computed: {
            selectedWidget() {

                return this.widgets.find(widget => widget.uriKey === this.selectedWidgetKey)

            },
            fields() {

                const options = this.widget.options || {}
                const fields = this.widget.schema.fields || []

                for (const attribute in options) {

                    const option = fields.find(option => option.attribute === attribute)

                    if (option) {

                        try {

                            option.value = JSON.parse(options[ attribute ])

                        } catch {

                            option.value = options[ attribute ]

                        }

                    }

                }

                return fields

            }
        },
        methods: {
            handleDelete() {

                this.working = true

                Nova.request({
                    method: 'post',
                    url: '/nova-vendor/nova-widgets/widget/delete',
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

                        this.working = false

                        Nova.error(this.__('There was a problem deleting the widget.'))

                    })

            },
            handleClose() {

                this.$emit('close')

            },
            handleConfirm() {

                this.working = true

                const formData = new FormData()

                for (const field of this.fields) field.fill(formData)

                Nova.request({
                    method: 'post',
                    url: '/nova-vendor/nova-widgets/widget/update',
                    params: {
                        id: this.widget.id,
                        dashboard: this.dashboardKey,
                        view: this.viewKey,
                        widget: this.widget.widgetKey
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

                        this.$emit('updated', this.widget.id, options)

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
