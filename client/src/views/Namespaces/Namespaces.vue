<script setup>
import { inject,onBeforeMount,ref,computed,watch } from 'vue'
import { useNodeStore } from '../../stores/node.js'
import { useNamespaceStore } from '../../stores/namespace.js'

const namespaceStore = useNamespaceStore()
const nodeStore = useNodeStore()

const axiosApi = inject('axiosApi')

const masterNodeID = ref(null) // Master Node ID

const loadMasterNodes = (() => { nodeStore.loadMasterNodes() })
const masterNodes = computed(() => { return nodeStore.getMasterNodes() })

const loadNamespaces = ((data) => { namespaceStore.loadNamespaces(data) })
const namespaces = computed(() => { return namespaceStore.getNamespaces() })

//Register a Namespace
const registerNamespace = () => {
    
    let formData = new FormData()

    formData.append('name', namespaces.value.name)
    formData.append('masterID', masterNodeID.value)

    namespaceStore.registerNamespace(formData)
   
}

//Delete a Namespace
const deleteNamespace = (namespace) => {

    namespaceStore.deleteNamespace(namespace,masterNodeID.value)

}


//Load Namespaces
watch(masterNodeID, () => {
    let data = { id: masterNodeID.value }
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
                <h2 class="p-title">Namespaces</h2>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="row">
                <div class="col-12">
                    <div class="card card-h-100">
                        <div class="d-flex card-header justify-content-between align-items-center">
                            <h4 class="header-title">Registered Namespaces</h4>
                        </div>
                        <div class="card-body pt-0">
                            <table class="table table-responsive align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th >Name</th>
                                        <th >Resouce Version</th>
                                        <th >Creation Time</th>
                                        <th>Manager</th>
                                        <th class="text-center" style="width:18%">Active</th>
                                        <th class="text-center" style="width:18%">Actions</th>
                                       
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="namespaces.length == 0">
                                        <td colspan="6" class="text-center" style="height:55px!important;">There are no Namespaces registered in the system.</td>
                                    </tr>
                                    <tr v-for="namespace in namespaces.items" :key="namespace.id">
                                        <td>{{namespace.metadata.name}}</td>
                                        <td>{{namespace.metadata.resourceVersion}}</td>
                                        <td>{{namespace.metadata.creationTimestamp}}</td>
                                        <td>{{namespace.metadata.managedFields[0].manager}}</td>
                                        <td v-if="namespace.status.phase!='Active'" class="text-center">
                                            <span class="badge badge-danger-lighten">Disabled</span>
                                        </td>
                                        <td v-else class="text-center">
                                            <span class="badge badge-success-lighten">Active</span>
                                        </td> 
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <button class="btn btn-xs btn-light table-button" title="Delete" @click="deleteNamespace(namespace)">
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
                    <h4 class="header-title">Register a new namespace</h4>
                </div>
                <div class="card-body pt-0">
                    <form class="row g-3 needs-validation" @submit.prevent="registerNamespace">
                        <div class="col-6">
                            <label for="name" class="form-label">Namespace name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" placeholder="Enter a name"
                             required :disabled="typeof masterNodeID === 'object'" v-model="namespaces.name">
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
