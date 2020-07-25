<template>

    <default-field :field="{ name: label, required }" :errors="errors">

        <template slot="field">

            <select-control
                    :id="id"
                    v-model="value"
                    class="w-full form-control form-select"
                    :options="options"
                    @input.native="$emit('input', value)">

                <option v-if="placeholder" value="" selected disabled>{{ placeholder }}</option>

            </select-control>

        </template>

    </default-field>

</template>

<script>

    export default {
        props: {
            label: { type: String, required: true },
            id: { type: String, required: true },
            required: { type: Boolean, default: false },
            options: { type: Array, default: () => [] },
            placeholder: { type: String, default: null }
        },
        data() {
            return {
                value: null
            }
        }
    }

</script>
