<template>
    <div>
        {{ data.content }}
    </div>
</template>

<script>
    export default {
        props: ['meta', 'data'],
        mounted() {
            console.log(this.meta)
            console.log(this.data)
        }
    }
</script>
