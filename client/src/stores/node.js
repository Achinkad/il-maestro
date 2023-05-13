import { inject, ref } from 'vue'
import { defineStore } from 'pinia'

export const useNodeStore = defineStore('node', () => {
    const axiosApi = inject('axiosApi') // Axios
    const notyf = inject('notyf') // Notyf

    const masterNodes = ref([]) // Master Nodes

    async function loadMasterNodes() {
        await axiosApi.get('nodes/master').then(response => {
            masterNodes.value = response.data
        }).catch(error => {
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
    }

    const getMasterNodes = (() => { return masterNodes.value })

    async function registerMasterNode(data) {
        await axiosApi.post('nodes/create', data).then((response) => {
            masterNodes.value.push(response.data.data)
            notyf.success('The master node was registered with success.')
        }).catch((error) => {
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
    }

    return {
        loadMasterNodes,
        getMasterNodes,
        registerMasterNode
    }
})
