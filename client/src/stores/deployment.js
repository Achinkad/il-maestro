import { inject, ref } from 'vue'
import { defineStore } from 'pinia'

export const useDeploymentStore = defineStore('deployment', () => {
    // Axios
    const axiosApi = inject('axiosApi')
    const notyf = inject('notyf') 

    // Array of deployments
    const deployments = ref([])
    
    // Load all of the deployments
    async function loadDeployments(id){
        
        await axiosApi.get('deployments', { params: id })
        .then((response) => {
            deployments.value = response.data

        }).catch(error => {
            
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
       
    }

    //Register a deployment
    async function registerDeployment(body){
        
        await axiosApi.post('deployments/create',body)
        .then((response) => {
        
            deployments.value.items.push(response.data)
            notyf.success('The Deployment was registered with success.')

        }).catch(error => {
            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
       
    }

    //Delete a deployment
    async function deleteDeployment(deployment,id){

    
        await axiosApi.delete('deployments/delete/'+deployment['metadata']['name'],{
            params:{
                deployment: deployment,
                id: id
            }
        }).then((response) => {
           
        let i = deployments.value.items.findIndex(element => element['metadata']['name'] === deployment['metadata']['name'])

        if (i >= 0) deployments.value.items.splice(i, 1);
            
        notyf.success('The Deployment was deleted with success.')
            
        }).catch((error) => {

            notyf.error(error.response.data + " (" + error.response.status + ")")
        })
       
    }

    const getDeployments = (() => {
        return deployments.value
    })

    
    return {
        loadDeployments,
        getDeployments,
        registerDeployment,
        deleteDeployment
       }
})
