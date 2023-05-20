import { inject, ref } from 'vue'
import { defineStore } from 'pinia'

export const useServiceStore = defineStore('service', () => {
    const axiosApi = inject('axiosApi') // Axios
    const notyf = inject('notyf') // Notyf

    const services = ref([]) // Services

    async function loadServices(body) {
        await axiosApi.get('services', { params: body }).then(response => {
            services.value = response.data
        }).catch(error => {
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
    }

    const getServices = (() => { return services.value })

    async function registerService(data) {
        await axiosApi.post('services/create', data).then((response) => {
            services.value.items.push(response.data)
            notyf.success('The service was registered with success.')
        }).catch((error) => {
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
    }

    async function deleteService(service, masterNode) {
        let data = { id: masterNode, namespace: service.metadata.namespace }

        await axiosApi.delete('services/delete/' + service.metadata.name, { params: data }).then(response => {
            notyf.success('The service was deleted with success.')

            // Remove from the array of services
            let index = services.value.items.indexOf(service)
            if (index > -1) services.value.items.splice(index, 1)

        }).catch((error) => {
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
    }

    return {
        loadServices,
        getServices,
        registerService,
        deleteService
    }
})
