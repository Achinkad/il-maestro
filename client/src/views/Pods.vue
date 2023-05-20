<script setup>
import { ref, inject, computed, watch, onBeforeMount, onMounted } from 'vue'
import { useNodeStore } from '../stores/node.js'
import { usePodStore } from '../stores/pod.js'
import { useNamespaceStore } from '../stores/namespace.js'

const axiosApi = inject('axiosApi')
const notyf = inject('notyf')

const podStore = usePodStore()
const nodeStore = useNodeStore()
const namespaceStore = useNamespaceStore()

const masterNodeID = ref(null) // Master node ID

const loadNamespaces = ((data) => { namespaceStore.loadNamespaces(data) })
const namespaces = computed(() => { return namespaceStore.getNamespaces() })

const loadMasterNodes = (() => { nodeStore.loadMasterNodes() })
const masterNodes = computed(() => { return nodeStore.getMasterNodes() })

const loadPods = ((data) => { podStore.loadPods(data) })
const pods = computed(() => { return podStore.getPods() })

const deletePod = ((pod) => { podStore.deletePod(pod, masterNodeID.value) })

// New POD reference
const pod = ref({ name: null, namespace: null, containerNumbers: 0 })
const container = ref([
    { name: null, image: null },
    { name: null, image: null },
    { name: null, image: null }
])

const registerPod = () => {
    let formData = new FormData()
    let containers = []

    formData.append('id', masterNodeID.value)
    formData.append('name', pod.value.name)
    formData.append('namespace', pod.value.namespace)

    for (var i = 0; i < pod.value.containerNumbers; i++) {
        containers.push({'name': container.value[i].name, 'image': container.value[i].image})
    }
    formData.append('containers', JSON.stringify(containers))

    podStore.registerPod(formData)

    pod.value.name = null
    pod.value.namespace = null
    pod.value.containerNumbers = 0
}

// Get all master nodes
onBeforeMount(() => {
    loadMasterNodes()
})

// Load pods
watch(masterNodeID, () => {
    let data = { id: masterNodeID.value }
    loadPods(data)
    loadNamespaces(data)
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
                <h2 class="p-title">Pods</h2>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="row">
                <div class="col-12">
                    <div class="card card-h-100">
                        <div class="d-flex card-header justify-content-between align-items-center">
                            <h4 class="header-title">pods available for the master node selected </h4>
                        </div>
                        <div class="card-body pt-0">
                            <table class="table table-responsive align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>UID</th>
                                        <th style="width:17%">Namespace</th>
                                        <th style="width:15%">Containers</th>
                                        <th class="text-center" style="width:11%">Running</th>
                                        <th class="text-center" style="width:15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="pods.length == 0">
                                        <td colspan="6" class="text-center" style="height:55px!important;">Please, select a master node to see all the pods.</td>
                                    </tr>
                                    <tr v-for="pod in pods.items" :key="pod.id">
                                        <td class="long-text-table" :title="pod.metadata.name">{{ pod.metadata.name }}</td>
                                        <td class="long-text-table" :title="pod.metadata.uid">{{ pod.metadata.uid }}</td>
                                        <td class="long-text-table" :title="pod.metadata.namespace">{{ pod.metadata.namespace }}</td>
                                        <td>{{ pod.spec.containers.length }}</td>
                                        <td v-if="pod.status.phase != 'Running'" class="text-center">
                                            <span class="badge badge-danger-lighten">Off</span>
                                        </td>
                                        <td v-else class="text-center">
                                            <span class="badge badge-success-lighten">On</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <button class="btn btn-xs btn-light table-button" title="Delete pod" @click="deletePod(pod)">
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
                    <h4 class="header-title">Register a pod</h4>
                </div>
                <div class="card-body pt-0">
                    <form class="row g-3 needs-validation" @submit.prevent="registerPod">
                        <div class="col-4">
                            <label for="name" class="form-label">Pod name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" placeholder="Enter a name"
                            v-model="pod.name" required :disabled="typeof masterNodeID === 'object'">
                        </div>
                        <div class="col-4">
                            <label for="namespace" class="form-label">Namespace associated <span class="text-danger">*</span></label>
                            <select class="form-select" id="namespace" v-model='pod.namespace' :disabled="typeof masterNodeID === 'object'">
                                <option value="null" selected hidden disabled>Select a namespace</option>
                                <option v-for="i in namespaces.items" :key="i" :value="i.metadata.name">{{i.metadata.name}}</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label for="containerNumbers" class="form-label">Number of containers <span class="text-danger">*</span></label>
                            <select class="form-select" id="containerNumbers" v-model='pod.containerNumbers' :disabled="typeof masterNodeID === 'object'">
                                <option value="0" selected hidden disabled>Select a number</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        <div v-for="index in parseInt(pod.containerNumbers)" :key="index" class="d-flex justify-content-between">
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
                                <button type="submit" class="btn btn-primary">Register pod</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="callout mt-0" v-if="typeof masterNodeID === 'object'">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Please select a master node in order to add a new pod.
            </div>
        </div>
    </div>
</template>
