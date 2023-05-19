import { inject, ref } from 'vue'
import { defineStore } from 'pinia'

export const useNodeStore = defineStore('node', () => {
    const axiosApi = inject('axiosApi') // Axios
    const notyf = inject('notyf') // Notyf

    const nodes = ref([]) // Nodes
    const masterNodes = ref([]) // Master Nodes

    async function loadMasterNodes() {
        await axiosApi.get('nodes/master').then(response => {
            masterNodes.value = response.data
        }).catch(error => {
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
    }

    const getMasterNodes = (() => { return masterNodes.value })

    async function loadNodes(body) {
        await axiosApi.get('nodes', { params: body }).then(response => {
            nodes.value = response.data
        }).catch(error => {
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
    }

    const getNodes = (() => { return nodes.value })

    async function registerMasterNode(data) {
        await axiosApi.post('nodes/create', data).then((response) => {
            masterNodes.value.push(response.data.data)
            notyf.success('The master node was registered with success.')
        }).catch((error) => {
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
    }

    async function deleteMasterNode(masterNode) {
        await axiosApi.delete('nodes/delete/' + masterNode.id).then(response => {
            notyf.success('The master node was deleted with success.')

            // Remove from the array of master nodes
            let index = masterNodes.value.indexOf(masterNode)
            if (index > -1) masterNodes.value.splice(index, 1)

        }).catch((error) => {
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
    }

    return {
        loadMasterNodes,
        getMasterNodes,
        loadNodes,
        getNodes,
        registerMasterNode,
        deleteMasterNode
    }
})
