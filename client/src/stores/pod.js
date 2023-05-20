import { inject, ref } from 'vue'
import { defineStore } from 'pinia'

export const usePodStore = defineStore('pod', () => {
    const axiosApi = inject('axiosApi') // Axios
    const notyf = inject('notyf') // Notyf

    const pods = ref([]) // Pods

    async function loadPods(body) {
        await axiosApi.get('pods', { params: body }).then(response => {
            pods.value = response.data
        }).catch(error => {
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
    }

    const getPods = (() => { return pods.value })

    async function registerPod(data) {
        await axiosApi.post('pods/create', data).then((response) => {
            pods.value.items.push(response.data)
            notyf.success('The pod was registered with success.')
        }).catch((error) => {
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
    }

    async function deletePod(pod, masterNode) {
        let data = { id: masterNode, namespace: pod.metadata.namespace }

        await axiosApi.delete('pods/delete/' + pod.metadata.name, { params: data }).then(response => {
            notyf.success('The pod was deleted with success.')

            // Remove from the array of master nodes
            let index = pods.value.items.indexOf(pod)
            if (index > -1) pods.value.items.splice(index, 1)

        }).catch((error) => {
            console.log(error)
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
    }

    return {
        loadPods,
        getPods,
        registerPod,
        deletePod
    }
})
