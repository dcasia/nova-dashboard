<template>

    <div class="nova-dashboard min-h-[auto pt-0">

        <Filter
            v-if="activeView"
            :lens="lens"
            :views="card.views"
            :active-view="activeView"
            :resource="resourceName"
            :resource-name="activeView.key"
            :columns="card.columns"
            :via-resource="viaResource"
            :via-resource-id="viaResourceId"
            :via-relationship="viaRelationship"
            @toggle="onViewChange"/>

        <Cards v-if="activeView && activeView.widgets.length" :cards="activeView.widgets"/>

    </div>

</template>

<script>

    import Filter from './Filter.vue'
    import resourceStore from '@/store/resources'

    export default {
        components: { Filter },
        props: [
            'card',
            'lens',
            'resourceName',
            'viaResource',
            'viaResourceId',
            'viaRelationship',
        ],
        data() {

            const key = Nova.store.getters[ 'queryStringParams' ].view
            const view = this.card.views.find(view => view.key === key)

            return {
                activeView: view ?? this.card.views[ 0 ],
            }

        },
        created() {

            for (const view of this.card.views) {

                for (const widget of view.widgets) {
                    widget.view = view.key
                }

                this.$store.registerModule(view.key, resourceStore)
                this.$store.commit(`${ view.key }/storeFilters`, view.filters)

            }

        },
        methods: {
            onViewChange(view) {
                this.activeView = view
            },
        },
    }

</script>
