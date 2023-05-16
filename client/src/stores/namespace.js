import { inject, ref } from 'vue'
import { defineStore } from 'pinia'

export const useNamespaceStore = defineStore('namespace', () => {
    // Axios
    const axiosApi = inject('axiosApi')
    const notyf = inject('notyf') 

    // Array of interfaces
    const namespaces = ref([])
    
    async function loadNamespaces(id){

        await axiosApi.get('namespaces', { params: id })
        .then((response) => {
            namespaces.value = response.data
            
        }).catch(error => {
            console.log(error)
            //notyf.error(error.response.data + " (" + error.response.status + ")")
        })
       
    }

    const getNamespaces = (() => {
        return namespaces.value
    })

    
    return {
        loadNamespaces,
        getNamespaces,
       }
})
