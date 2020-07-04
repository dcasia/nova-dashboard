<template>

    <div class="nova-bi">

        <button @click="editMode = !editMode">Toggle</button>

        <component class="flex flex-col inline-flex"
                   v-for="filter in filters"
                   :key="filter.name"
                   :resource-name="resourceName"
                   :filter-key="filter.class"
                   :is="filter.component"
                   @input="filterChanged"
                   @change="filterChanged"/>

        <chacheli-designer v-if="editMode" ref="designer" :layout="layout" :chachelis="widgets"/>
        <chacheli-layout v-if="!editMode" :layout="layout" :chachelis="widgets" :data="data"/>

    </div>

</template>

<script>

    import ChacheliDesigner from '@shellybits/v-chacheli/dist/ChacheliDesigner'
    import ChacheliLayout from '@shellybits/v-chacheli/dist/ChacheliLayout'
    import '@shellybits/v-chacheli/dist/ChacheliDesigner.css'
    import '@shellybits/v-chacheli/dist/ChacheliLayout.css'
    import resource from '~~nova~~/store/resources'

    export default {
        name: 'app',
        components: { ChacheliDesigner, ChacheliLayout },
        data() {

            const resourceName = this.$route.params.resource
            const { columns, rows, widgets, filters } = Nova.config[ 'nova-bi' ]

            this.$store.registerModule(resourceName, resource)
            this.$store.commit(`${ resourceName }/storeFilters`, filters)

            return {
                filters,
                resourceName,
                widgets,
                idCounter: 10,
                layout: {
                    cols: columns,
                    rows: rows
                },
                editMode: false,
                data: {}
            }
        },
        methods: {
            filterChanged() {

                Nova.$emit('NovaFilterUpdate', this.$store.getters[ `${ this.resourceName }/currentEncodedFilters` ])

            }
        }
    }

</script>

<style lang="scss">

    [data-testid="content"] {
        height: calc(100% - 3.75rem);
    }

    .chacheli-layout .chacheli > * {
        height: 100%;
        display: flex;
        overflow: hidden;
    }

    .nova-bi {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .chacheli .content {
        min-width: initial;
        width: initial;
        max-width: initial;
    }
</style>

