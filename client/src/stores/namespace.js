import { inject, ref } from 'vue'
import { defineStore } from 'pinia'

export const useNamespaceStore = defineStore('namespace', () => {
    // Axios
    const axiosApi = inject('axiosApi')
    const notyf = inject('notyf') 

    // Array of namespaces
    const namespaces = ref([])
    
    // Load all of the namespaces
    async function loadNamespaces(id){

        await axiosApi.get('namespaces', { params: id })
        .then((response) => {
            namespaces.value = response.data
        }).catch(error => {
            console.log(response.data)
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
       
    }

    //Register a namespace
    async function registerNamespace(body){

        await axiosApi.post('namespaces/create',body)
        .then((response) => {
        
            namespaces.value.items.push(response.data)
            notyf.success('The Namespace was registered with success.')

        }).catch(error => {
            console.log(error)
            //notyf.error(error.response.data + " (" + error.response.status + ")")
        })
       
    }

    //Delete a namespace
    async function deleteNamespace(namespace,id){

   
        await axiosApi.delete('namespaces/delete/'+namespace['metadata']['name'],{
            params:{
                namespace: namespace,
                id: id
            }
        }).then((response) => {
           
        let i = namespaces.value.items.findIndex(element => element['metadata']['name'] === namespace['metadata']['name'])

        if (i >= 0) namespaces.value.items.splice(i, 1);
            
        notyf.success('The Namespace was deleted with success.')
            
        }).catch((error) => {

            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
       
    }

    const getNamespaces = (() => {
        return namespaces.value
    })

    
    return {
        loadNamespaces,
        getNamespaces,
        registerNamespace,
        deleteNamespace
       }
})
