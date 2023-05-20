<script setup>
import { inject,onBeforeMount,ref,computed,watch } from 'vue'
import { useNodeStore } from '../stores/node.js'
import { useDeploymentStore } from '../stores/deployment.js'
import { useNamespaceStore } from '../stores/namespace.js'

const deploymentStore = useDeploymentStore()
const namespaceStore = useNamespaceStore()
const nodeStore = useNodeStore()

const axiosApi = inject('axiosApi')

const masterNodeID = ref(null) // Master Node ID

const loadMasterNodes = (() => { nodeStore.loadMasterNodes() })
const masterNodes = computed(() => { return nodeStore.getMasterNodes() })

const loadNamespaces = ((data) => { namespaceStore.loadNamespaces(data) })
const namespaces = computed(() => { return namespaceStore.getNamespaces() })

const loadDeployments = ((data) => { deploymentStore.loadDeployments(data) })
const deployments = computed(() => { return deploymentStore.getDeployments() })

// New Deployment reference
const deployment = ref({ name: null, namespace: null, key_label: null, value_label: null, containerNumbers: 0 })
const container = ref([
    { name: null, image: null },
    { name: null, image: null },
    { name: null, image: null }
])

//Register a Deployment
const registerDeployment = () => {
    
    let formData = new FormData()
    let containers = []

    formData.append('name', deployment.value.name)
    formData.append('namespace', deployment.value.namespace)
    formData.append(deployment.value.key_label, deployment.value.value_label)
    formData.append('id', masterNodeID.value)

    for (var i = 0; i < deployment.value.containerNumbers; i++) {
        containers.push({'name': container.value[i].name, 'image': container.value[i].image})
    }
    formData.append('containers', JSON.stringify(containers))

    
    deploymentStore.registerDeployment(formData)

    deployment.value.name = null
    deployment.value.namespace = null
    deployment.value.key_label = null
    deployment.value.value_label = null
    deployment.value.containerNumbers = 0
   
}

//Delete a Deployment
const deleteDeployment = (deployment) => {


    deploymentStore.deleteDeployment(deployment,masterNodeID.value)

}


//Load Namespaces
watch(masterNodeID, () => {
    let data = { id: masterNodeID.value }
    loadDeployments(data)
    loadNamespaces(data)
})

// Get All Master Nodes
onBeforeMount(() => {
    loadMasterNodes()
})

</script>

<template>
     <div class="row">
        <div class="col-12">
            <div class="p-title-box">
                <div class="p-title-right" style="width:15%;">
                    <select class="form-select" v-model="masterNodeID">
                        <option value="null" selected hidden disabled v-if="masterNodes.length > 0">Select a master node</option>
                        <option value="null" selected hidden disabled v-else>Loading master nodes...</option>
                        <option v-for="node in masterNodes" :key="node.id" :value="node.id" :disabled="node.disabled">{{ node.ip_address }}</option>
                    </select>
                </div>
                <h2 class="p-title">Deployments</h2>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="row">
                <div class="col-12">
                    <div class="card card-h-100">
                        <div class="d-flex card-header justify-content-between align-items-center">
                            <h4 class="header-title">Deployments available for the master node selected</h4>
                        </div>
                        <div class="card-body pt-0">
                            <table class="table table-responsive align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th >Name</th>
                                        <th >Namespace</th>
                                        <th>Manager</th>
                                        <th class="text-center" style="width:18%">Available</th>
                                        <th class="text-center" style="width:18%">Progressing</th>
                                        <th class="text-center" style="width:14%">Actions</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="deployments.length == 0">
                                        <td colspan="6" class="text-center" style="height:55px!important;">Please, select a master node to see all the deployments.</td>
                                    </tr>
                                    <tr v-for="deployment in deployments.items" :key="deployment.id">
                                        <td class="long-text-table">{{deployment.metadata.name}}</td>
                                        <td class="long-text-table">{{deployment.metadata.namespace}}</td>
                                        <td class="long-text-table">{{deployment.metadata.managedFields[0].manager}}</td>

                                        <td v-if="deployment.status.conditions==undefined" class="text-center">
                                            <span class="badge badge-warning-lighten">Unavailable</span>
                                        </td>
                                        <td v-else-if="deployment.status.conditions[0].status!='True'" class="text-center">
                                            <span class="badge badge-danger-lighten">False</span>
                                        </td>
                                        <td v-else class="text-center">
                                            <span class="badge badge-success-lighten">True</span>
                                        </td>

                                        <td v-if="deployment.status.conditions==undefined" class="text-center">
                                            <span class="badge badge-warning-lighten">Unavailable</span>
                                        </td>
                                        <td v-else-if="deployment.status.conditions[1].status!='True'" class="text-center">
                                            <span class="badge badge-danger-lighten">False</span>
                                        </td>
                                        <td v-else class="text-center">
                                            <span class="badge badge-success-lighten">True</span>
                                        </td>

                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <button class="btn btn-xs btn-light table-button" title="Delete" @click="deleteDeployment(deployment)">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card card-h-100">
                <div class="d-flex card-header justify-content-between align-items-center">
                    <h4 class="header-title">Register a new deployment</h4>
                </div>
                <div class="card-body pt-0">
                    <form class="row g-3 needs-validation" @submit.prevent="registerDeployment">
                        <div class="col-6">
                            <label for="name" class="form-label">Deployment name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" placeholder="Enter a name"
                             required :disabled="typeof masterNodeID === 'object'" v-model="deployment.name">
                        </div>

                        <div class="col-6">
                            <label for="namespace" class="form-label">Namespace associated <span class="text-danger">*</span></label>
                            <select class="form-select" id="namespace" v-model='deployment.namespace' :disabled="typeof masterNodeID === 'object'">
                                <option value="null" selected hidden disabled>Select a namespace</option>
                                <option v-for="i in namespaces.items" :key="i" :value="i.metadata.name">{{i.metadata.name}}</option>
                            </select>
                        </div>

                         <!--Label Key-->
                        <div class="col-6">
                        <label for="name" class="form-label">Label Key<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" placeholder="Enter key"
                            required :disabled="typeof masterNodeID === 'object'" v-model="deployment.key_label">
                        </div>
                        <!--Label Value-->
                        <div class="col-6">
                        <label for="name" class="form-label">Label Value<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" placeholder="Enter value"
                            required :disabled="typeof masterNodeID === 'object'" v-model="deployment.value_label">
                        </div>
                      
                        <div class="col-12">
                            <label for="containerNumbers" class="form-label">Number of containers <span class="text-danger">*</span></label>
                            <select class="form-select" id="containerNumbers" v-model='deployment.containerNumbers' :disabled="typeof masterNodeID === 'object'">
                                <option value="0" selected hidden disabled>Select a number</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>

                         <div v-for="index in parseInt(deployment.containerNumbers)" :key="index" class="d-flex justify-content-between">
                            <div class="col-6" style="width:47%;">
                                <label for="containerName" class="form-label">Container name #{{ index }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" :id="'containerName' + index" placeholder="Enter a container name"
                                v-model="container[index - 1].name" required :disabled="typeof masterNodeID === 'object'">
                            </div>
                            <div class="col-6">
                                <label for="containerImage" class="form-label">Container image #{{ index }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" :id="'containerImage' + index" placeholder="Enter a container image"
                                v-model="container[index - 1].image" required :disabled="typeof masterNodeID === 'object'">
                            </div>
                        </div>

                        <div class="col-12 mt-4 d-flex justify-content-end">
                            <div class="px-1">
                                <button type="reset" class="btn btn-light px-4 me-1">Clear</button>
                            </div>
                            <div class="px-1">
                                <button type="submit" class="btn btn-primary">Register Namespace</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
