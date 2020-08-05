<template>

    <div class="tabs">

        <div class="flex w-full select-none border-b border-40">

            <div v-for="header of headers"
                 :key="header.key"
                 class="py-4 px-6 border-b-2 border-transparent cursor-pointer text-60 font-bold"
                 :class="{'border-b-2 border-primary text-90': activeTab === header.key }"
                 @click="setActiveTab(header.key)">

                <div>
                    {{ header.title }}
                </div>

            </div>

        </div>

        <slot v-for="header of headers" :name="header.key"
              :show="header.key === activeTab"/>

    </div>

</template>

<script>

export default {
    name: 'Tabs',
    props: {
        headers: { type: Array, required: true }
    },
    data() {

        return {
            activeTab: this.headers[ 0 ].key
        }

    },
    methods: {
        setActiveTab(key) {

            if (key !== this.activeTab) {

                this.activeTab = key

            }

        }
    }
}

</script>
