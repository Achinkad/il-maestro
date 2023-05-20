<script setup>
import { ref, inject, computed, watch, onBeforeMount, onMounted } from 'vue'
import { useNodeStore } from '../stores/node.js'
import { useServiceStore } from '../stores/service.js'
import { useNamespaceStore } from '../stores/namespace.js'

const axiosApi = inject('axiosApi')
const notyf = inject('notyf')

const serviceStore = useServiceStore()
const nodeStore = useNodeStore()
const namespaceStore = useNamespaceStore()

const masterNodeID = ref(null) // Master node ID

const loadNamespaces = ((data) => { namespaceStore.loadNamespaces(data) })
const namespaces = computed(() => { return namespaceStore.getNamespaces() })

const loadMasterNodes = (() => { nodeStore.loadMasterNodes() })
const masterNodes = computed(() => { return nodeStore.getMasterNodes() })

const loadServices = ((data) => { serviceStore.loadServices(data) })
const services = computed(() => { return serviceStore.getServices() })

const deleteService = ((service) => { serviceStore.deleteService(service, masterNodeID.value) })

// New SERVICE reference
const service = ref({ name: null, namespace: null, portsNumber: 0 })
const port = ref([
    { name: null, protocol: null, port: null },
    { name: null, protocol: null, port: null },
    { name: null, protocol: null, port: null }
])

const registerService = () => {
    let formData = new FormData()
    let ports = []

    formData.append('id', masterNodeID.value)
    formData.append('name', service.value.name)
    formData.append('namespace', service.value.namespace)

    for (var i = 0; i < service.value.portsNumber; i++) {
        ports.push({
            'name': port.value[i].name,
            'protocol': port.value[i].protocol,
            'port': parseInt(port.value[i].port)
        })
    }
    formData.append('ports', JSON.stringify(ports))

    serviceStore.registerService(formData)

    service.value.name = null
    service.value.namespace = null
    service.value.portsNumber = 0
}

const getAllPorts = ((ports) => {
    let string = ""
    ports.forEach((i) => {
        string += `${i.port}, `
    })
    return string.slice(0, -2)
})

// Get all master nodes
onBeforeMount(() => {
    loadMasterNodes()
})

// Load SERVICES
watch(masterNodeID, () => {
    let data = { id: masterNodeID.value }
    loadServices(data)
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
                <h2 class="p-title">Services</h2>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="row">
                <div class="col-12">
                    <div class="card card-h-100">
                        <div class="d-flex card-header justify-content-between align-items-center">
                            <h4 class="header-title">Services available for the master node selected </h4>
                        </div>
                        <div class="card-body pt-0">
                            <table class="table table-responsive align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:15%">Name</th>
                                        <th>UID</th>
                                        <th style="width:17%">Namespace</th>
                                        <th style="width:19%">Ports</th>
                                        <th class="text-center" style="width:15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="services.length == 0">
                                        <td colspan="5" class="text-center" style="height:55px!important;">Please, select a master node to see all the services.</td>
                                    </tr>
                                    <tr v-for="service in services.items" :key="service.id">
                                        <td class="long-text-table" :title="service.metadata.name">{{ service.metadata.name }}</td>
                                        <td class="long-text-table" :title="service.metadata.uid">{{ service.metadata.uid }}</td>
                                        <td class="long-text-table" :title="service.metadata.namespace">{{ service.metadata.namespace }}</td>
                                        <td>{{ service.spec.ports.length }} <span>({{ getAllPorts(service.spec.ports) }})</span> </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <button class="btn btn-xs btn-light table-button" title="Delete service" @click="deleteService(service)">
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
                    <h4 class="header-title">Register a service</h4>
                </div>
                <div class="card-body pt-0">
                    <form class="row g-3 needs-validation" @submit.prevent="registerService">
                        <div class="col-4">
                            <label for="name" class="form-label">Service name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" placeholder="Enter a name"
                            v-model="service.name" required :disabled="typeof masterNodeID === 'object'">
                        </div>
                        <div class="col-4">
                            <label for="namespace" class="form-label">Namespace associated <span class="text-danger">*</span></label>
                            <select class="form-select" id="namespace" v-model='service.namespace' :disabled="typeof masterNodeID === 'object'">
                                <option value="null" selected hidden disabled>Select a namespace</option>
                                <option v-for="i in namespaces.items" :key="i" :value="i.metadata.name">{{i.metadata.name}}</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label for="portsNumber" class="form-label">Number of ports <span class="text-danger">*</span></label>
                            <select class="form-select" id="portsNumber" v-model='service.portsNumber' :disabled="typeof masterNodeID === 'object'">
                                <option value="0" selected hidden disabled>Select a number</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        <div v-for="index in parseInt(service.portsNumber)" :key="index">
                            <div class="row g-3">
                                <div class="col-4">
                                    <label for="portName" class="form-label">Port name #{{ index }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" :id="'portName' + index" placeholder="Enter a port name"
                                    v-model="port[index - 1].name" required :disabled="typeof masterNodeID === 'object'">
                                </div>
                                <div class="col-4">
                                    <label for="portProtocol" class="form-label">Port protocol #{{ index }} <span class="text-danger">*</span></label>
                                    <select class="form-select" id="portProtocol" v-model="port[index - 1].protocol" :disabled="typeof masterNodeID === 'object'">
                                        <option value="null" selected hidden disabled>Select a protocol</option>
                                        <option value="TCP">TCP</option>
                                        <option value="UDP">UDP</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label for="portNumber" class="form-label">Port number #{{ index }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" :id="'portNumber' + index" placeholder="Enter a container image"
                                    v-model="port[index - 1].port" required :disabled="typeof masterNodeID === 'object'">
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-4 d-flex justify-content-end">
                            <div class="px-1">
                                <button type="reset" class="btn btn-light px-4 me-1">Clear</button>
                            </div>
                            <div class="px-1">
                                <button type="submit" class="btn btn-primary">Register service</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="callout mt-0" v-if="typeof masterNodeID === 'object'">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Please select a master node in order to add a new service.
            </div>
        </div>
    </div>
</template>
