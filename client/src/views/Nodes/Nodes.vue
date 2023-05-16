<script setup>
import { ref, inject, computed, watch, onBeforeMount } from 'vue'
import { useNodeStore } from '../../stores/node.js'

const axiosApi = inject('axiosApi')
const notyf = inject('notyf')

const nodeStore = useNodeStore()

const masterNodeID = ref(null) // Master Node ID

const loadMasterNodes = (() => { nodeStore.loadMasterNodes() })
const masterNodes = computed(() => { return nodeStore.getMasterNodes() })

const loadNodes = ((data) => { nodeStore.loadNodes(data) })
const nodes = computed(() => { return nodeStore.getNodes() })

// Copy bearer token into the clipboard
const copy = ((node) => {
    navigator.clipboard.writeText(node.token)
    notyf.open({type: 'info', message: 'The bearer token is now on your clipboard. Go paste it!'})
})

// Get all master nodes
onBeforeMount(() => {
    loadMasterNodes()
})

// Load all nodes
watch(masterNodeID, () => {
    let data = { id: masterNodeID.value }
    loadNodes(data)
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
                <h2 class="p-title">All Nodes</h2>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="row">
                <div class="col-12">
                    <div class="card card-h-100">
                        <div class="d-flex card-header justify-content-between align-items-center">
                            <h4 class="header-title">All nodes</h4>
                        </div>
                        <div class="card-body pt-0">
                            <!-- {{ nodes.items[1].metadata.name }} -->
                            <table class="table table-responsive align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:18%">Hostname</th>
                                        <th style="width:18%">IP address</th>
                                        <th style="width:12%">Port</th>
                                        <th>UID</th>
                                        <th class="text-center" style="width:15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="nodes.length == 0">
                                        <td colspan="5" class="text-center" style="height:55px!important;">There are no master nodes registered in the system.</td>
                                    </tr>
                                    <tr v-for="node in nodes.items" :key="node.id">
                                        <td>{{ node.metadata.name }}</td>
                                        <td>{{ node.status.addresses[0].address }}</td>
                                        <td>6443</td>
                                        <td>{{ node.metadata.uid }}</td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <button class="btn btn-xs btn-light table-button" title="Edit">
                                                    <i class="bi bi-eye"></i>
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
                    <h4 class="header-title">Node details</h4>
                </div>
                <div class="card-body pt-0">
                    <p>Form here.</p>
                </div>
            </div>
        </div>
    </div>
</template>
