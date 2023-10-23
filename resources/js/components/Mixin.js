export function registerMixin(component) {

    component.mixins = component.mixins || []

    component.mixins.push({
        mounted() {

            Nova.$on(`${ this.card.view }-updated`, (encoded, resource) => {

                const data = new FormData

                data.append(`${ this.card.view }_filter`, encoded)
                data.append('widget', this.card.key)
                data.append('view', this.card.view)

                const extraParam = resource ? `/${ resource }` : ''

                Nova.request({
                    method: 'post',
                    url: `/nova-vendor/nova-dashboard/widget/update${ extraParam }`,
                    data,
                }).then(response => this.card.value = response.data.value)

            })

        },
    })

}

export function registerDashboardMixin(component) {

    const originalDashboardEndpoint = component.computed.dashboardEndpoint

    component.computed.dashboardEndpoint = function () {
        return originalDashboardEndpoint.call(this) + window.location.search
    }

}
