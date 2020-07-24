<template>

    <modal @modal-close="handleClose">

        <form @submit.prevent="handleConfirm"
              slot-scope="props"
              class="bg-white rounded-lg shadow-lg overflow-hidden"
              style="width: 800px;">

            <div class="p-8 pb-0">
                <heading :level="2" class="mb-6">{{ __('Select Action') }}</heading>
            </div>

            <default-field :field="selectInput" :errors="errors">

                <template slot="field">

                    <select-control
                            :id="selectInput.attribute"
                            v-model="key"
                            class="w-full form-control form-select"
                            :class="errorClasses"
                            :options="selectInput.options"
                            @input.native="onActionSelected">

                        <option value="" selected disabled>{{ __('Choose an action') }}</option>

                    </select-control>

                </template>

            </default-field>

            <div v-for="field in fields">

                <component :is="'form-' + field.component"
                           :resource-name="resourceName"
                           :field="field"
                           :errors="errors"/>

            </div>

            <div class="bg-30 px-6 py-3 flex">

                <div class="ml-auto">

                    <button type="button"
                            @click.prevent="handleClose"
                            class="btn text-80 font-normal h-9 px-3 mr-3 btn-link">

                        {{ selectedAction ? selectedAction.cancelButtonText : __('Cancel') }}

                    </button>

                    <progress-button :disabled="!selectedAction || working"
                                     @click.native="handleConfirm"
                                     :processing="working">

                        {{ selectedAction ? selectedAction.confirmButtonText : __('Select an action.') }}

                    </progress-button>

                </div>

            </div>

        </form>

    </modal>

</template>

<script>

    import { Errors } from 'laravel-nova'

    export default {
        props: {
            actions: { type: Array, default: () => [] },
            dashboard: { type: String, required: true }
        },
        data() {

            return {
                key: null,
                working: false,
                errors: new Errors,
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
                    options: this.actions.map(action => {

                        return {
                            label: action.name,
                            value: action.uriKey
                        }

                    })
                }
            }

        },
        computed: {
            selectedAction() {

                return this.actions.find(action => action.uriKey === this.key)

            },
            fields() {

                if (this.selectedAction) {

                    return this.selectedAction.fields || []

                }

                return []

            }
        },
        methods: {
            handleClose() {

                this.$emit('close')

            },
            onActionSelected() {

                this.selectedAction = this.value

            },
            handleConfirm() {

                this.working = true

                const formData = new FormData()

                for (const field of this.fields) {

                    field.fill(formData)

                }

                Nova.request({
                    method: 'post',
                    url: `/nova-vendor/nova-widgets/action/${ this.dashboard }/${ this.selectedAction.uriKey }`,
                    params: {
                        filters: this.$store.getters[ `${ this.dashboard }/currentEncodedFilters` ]
                    },
                    data: formData
                })
                    .then(response => {
                        this.handleActionResponse(response.data)
                        this.handleClose()
                        this.working = false
                    })
                    .catch(error => {
                        this.working = false

                        if (error.response.status === 422) {
                            this.errors = new Errors(error.response.data.errors)
                            Nova.error(this.__('There was a problem executing the action.'))
                        }
                    })

            },

            /**
             * Copied from HandlesActions.js
             * Handle the action response. Typically either a message, download or a redirect.
             */
            handleActionResponse(data) {

                if (data.message) {

                    this.$emit('actionExecuted')
                    Nova.$emit('action-executed')
                    Nova.success(data.message)

                } else if (data.deleted) {

                    this.$emit('actionExecuted')
                    Nova.$emit('action-executed')

                } else if (data.danger) {

                    this.$emit('actionExecuted')
                    Nova.$emit('action-executed')
                    Nova.error(data.danger)

                } else if (data.download) {

                    let link = document.createElement('a')
                    link.href = data.download
                    link.download = data.name
                    document.body.appendChild(link)
                    link.click()
                    document.body.removeChild(link)

                } else if (data.redirect) {

                    window.location = data.redirect

                } else if (data.push) {

                    this.$router.push(data.push)

                } else if (data.openInNewTab) {

                    window.open(data.openInNewTab, '_blank')

                } else {

                    this.$emit('actionExecuted')
                    Nova.$emit('action-executed')
                    Nova.success(this.__('The action ran successfully!'))

                }

            }

        }

    }

</script>
