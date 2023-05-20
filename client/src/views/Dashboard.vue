<script setup>
import { inject, computed, onBeforeMount, watch,ref } from 'vue'

import { useNodeStore } from '../stores/node.js'
import { useUserStore } from "../stores/user.js"
import { useNamespaceStore } from '../stores/namespace.js'
import { useDeploymentStore } from '../stores/deployment.js'
import { usePodStore } from '../stores/pod.js'

const deploymentStore = useDeploymentStore()
const namespaceStore = useNamespaceStore()
const nodeStore = useNodeStore()
const podStore = usePodStore()
const userStore = useUserStore()

const axiosApi = inject('axiosApi')
const notyf = inject('notyf')

const loadNodes = (() => { nodeStore.loadNodes() })
const nodes = computed(() => { return nodeStore.getNodes() })

const loadNamespaces = ((data) => { namespaceStore.loadNamespaces(data) })
const namespaces = computed(() => { return namespaceStore.getNamespaces() })

const loadPods = ((data) => { podStore.loadPods(data) })
const pods = computed(() => { return podStore.getPods() })

const loadDeployments = ((data) => { deploymentStore.loadDeployments(data) })
const deployments = computed(() => { return deploymentStore.getDeployments() })

const loadMasterNodes = (() => { nodeStore.loadMasterNodes() })
const masterNodes = computed(() => { return nodeStore.getMasterNodes() })

const numberNodes = ref(0)
const numberActiveNodes = ref(0)
const percentActiveNodes = ref(0)

const numberNamespaces = ref(0)
const numberActiveNamespaces = ref(0)
const percentActiveNamespaces = ref(0)

const numberPods = ref(0)
const numberActivePods = ref(0)
const percentActivePods = ref(0)

const numberDeployments = ref(0)
const numberActiveDeployments = ref(0)
const percentActiveDeployments = ref(0)

watch(nodes, () => {
    nodes.value.forEach(node => {
        numberNodes.value = numberNodes.value + node.items.length
        node.items.forEach( nodeItem => {
            if (nodeItem.status.conditions[3].status=='True') { numberActiveNodes.value++ }
        })
    })

    if (numberNodes.value > 0) {
        percentActiveNodes.value = numberActiveNodes.value / numberNodes.value * 100
    }
})

watch(namespaces, () => {
    namespaces.value.forEach(namespace => {
        numberNamespaces.value = numberNamespaces.value + namespace.items.length
        namespace.items.forEach( namespaceItem => {
            if (namespaceItem.status.phase=='Active') { numberActiveNamespaces.value++ }
        })
    })

    if (numberNamespaces.value > 0) {
        percentActiveNamespaces.value = numberActiveNamespaces.value / numberNamespaces.value * 100
    }
})

watch(deployments, () => {
    deployments.value.forEach(deployment => {
        numberDeployments.value = numberDeployments.value + deployment.items.length
        deployment.items.forEach( deploymentItem => {
            if (deploymentItem.status.conditions[1].status=='True') {
                numberActiveDeployments.value++
            }
        })
    })

    if (numberDeployments.value > 0) {
        percentActiveDeployments.value = numberActiveDeployments.value / numberDeployments.value * 100
    }
})

watch(pods, () => {
    pods.value.forEach(pod => {
        numberPods.value = numberPods.value + pod.items.length
        pod.items.forEach( podItem => {
            if (podItem.status.phase == 'Running'){
                numberActivePods.value++
            }
        })
    })

    if (numberPods.value > 0) {
        percentActivePods.value = numberActivePods.value / numberPods.value * 100
    }
})

onBeforeMount(() => {
    let data = { id: 0 }
    loadNodes(data)
    loadNamespaces(data)
    loadPods(data)
    loadDeployments(data)
    loadMasterNodes()
})

const metrics = ((node) => {
    let body = { id: node.id }

    axiosApi.get('metrics', { params: body }).then(response => {
        let fileURL = window.URL.createObjectURL(new Blob([response.data]))
        let fileLink = document.createElement('a')

        fileLink.href = fileURL
        fileLink.setAttribute('download', 'metrics.txt')
        document.body.appendChild(fileLink)
        fileLink.click()

        notyf.success('The metrics file was downloaded with success.')
    }).catch(error => {
        notyf.error(error.response.data + " (" + error.response.status + ")")
    })
})

const logs = ((node) => {
    let body = { id: node.id }

    axiosApi.get('logs', { params: body }).then(response => {
        let fileURL = window.URL.createObjectURL(new Blob([response.data]))
        let fileLink = document.createElement('a')

        fileLink.href = fileURL
        fileLink.setAttribute('download', 'logs.txt')
        document.body.appendChild(fileLink)
        fileLink.click()

        notyf.success('The logs file was downloaded with success.')
    }).catch(error => {
        notyf.error(error.response.data + " (" + error.response.status + ")")
    })
})
</script>

<template>
    <div class="row">
        <div class="col-12">
            <div class="p-title-box">
                <h2 class="p-title">Dashboard</h2>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-5 col-lg-12">
            <div class="row">
                <div class="col-md-12 col-lg-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="bi bi-gear card-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Nodes</h5>
                            <h3 v-if="numberNodes>0" class="mt-3 mb-3">{{numberNodes}}</h3>
                            <h3 v-else class="mt-3 mb-3">-</h3>
                            <p class="mb-0 text-muted">
                                <span v-if="numberNodes>0" class="text-success me-2"> {{percentActiveNodes.toFixed(0)}}% </span>
                                <span v-else class="text-success me-2"> - % </span>
                                <span class="text-nowrap">Active right now</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="bi bi-square card-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Namespaces</h5>
                            <h3 v-if="numberNamespaces>0" class="mt-3 mb-3">{{numberNamespaces}}</h3>
                            <h3 v-else class="mt-3 mb-3">-</h3>
                            <p class="mb-0 text-muted">
                                <span v-if="numberNamespaces>0" class="text-success me-2">{{percentActiveNamespaces.toFixed(0)}}% </span>
                                <span v-else class="text-success me-2"> - % </span>
                                <span class="text-nowrap">Active right now</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="bi bi-box-fill card-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Pods</h5>
                            <h3 v-if="numberPods>0" class="mt-3 mb-3">{{numberPods}}</h3>
                            <h3 v-else class="mt-3 mb-3">-</h3>
                            <p class="mb-0 text-muted">
                                <span v-if="numberPods>0" class="text-success me-2">{{percentActivePods.toFixed(0)}}% </span>
                                <span v-else class="text-success me-2"> - % </span>
                                <span class="text-nowrap">Active right now</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="bi bi-arrow-clockwise card-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Deployments</h5>
                            <h3 v-if="numberDeployments>0" class="mt-3 mb-3">{{numberDeployments}}</h3>
                            <h3 v-else class="mt-3 mb-3">-</h3>
                            <p class="mb-0 text-muted">
                                <span v-if="numberDeployments>0" class="text-success me-2">{{percentActiveDeployments.toFixed(0)}}% </span>
                                <span v-else class="text-success me-2"> - % </span>
                                <span class="text-nowrap">Active right now</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-7 col-lg-7">
            <div class="card card-h-100">
                <div class="d-flex card-header justify-content-between align-items-center">
                    <h4 class="header-title">Registered master nodes</h4>
                </div>

                <div class="card-body pt-0">
                    <table class="table table-responsive align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:8%">#ID</th>
                                <th style="width:18%">Node name</th>
                                <th style="width:18%">IP address</th>
                                <th style="width:12%">Port</th>
                                <th class="text-center" style="width:10%">Active</th>
                                <th class="text-center" style="width:15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="masterNodes.length == 0">
                                <td colspan="6" class="text-center" style="height:55px!important;">There are no master nodes registered in the system.</td>
                            </tr>
                            <tr v-for="node in masterNodes" :key="node.id">
                                <td class="align-middle" style="height:55px!important;">#{{ node.id }}</td>
                                <td>{{ node.name }}</td>
                                <td>{{ node.ip_address }}</td>
                                <td>{{ node.port }}</td>
                                <td class="text-center" v-if="node.disabled">
                                    <span class="badge badge-danger-lighten">Disabled</span>
                                </td>
                                <td class="text-center" v-else>
                                    <span class="badge badge-success-lighten">Active</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        <button class="btn btn-xs btn-light table-button" title="Metrics" @click="metrics(node)">
                                            <i class="bi bi-graph-up"></i>
                                        </button>
                                        <button class="btn btn-xs btn-light table-button ms-2" title="Logs" @click="logs(node)">
                                            <i class="bi bi-binoculars"></i>
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
</template>

<style scoped>
.widget-flat {
    position: relative;
    overflow: hidden;
}

.card {
    border: none;
    margin-bottom: 24px;
    box-shadow: 0 0 35px 0 rgba(154, 161, 171, 0.15) !important;
    border-radius: 3px;
}

.card-header {
    margin-top: 0;
    background-color: #fff;
    border: 0;
    margin-bottom: 0;
    padding: 1.5rem;
}

.card-header .header-title {
    text-transform: uppercase;
    letter-spacing: 0.02em;
    font-size: 0.9rem;
    margin-top: 0;
}

.card-body {
    padding: 1.5rem;
}

.card-icon {
    color: #727cf5;
    font-size: 16px;
    background-color: rgba(114, 124, 245, 0.25);
    height: 40px;
    width: 40px;
    text-align: center;
    line-height: 40px;
    border-radius: 3px;
    display: inline-block;
}

.text-muted {
    color: #8a969c !important;
}

.mt-3 {
    margin-top: 1.5rem !important;
}

.mb-3 {
    margin-bottom: 1.5rem !important;
}

.me-2 {
    margin-right: 0.75rem !important;
}

.text-success {
    color: rgb(10, 207, 151) !important;
}

.text-danger {
    color: rgb(250, 92, 124) !important;
}

#arrow-icons {
    font-size: 14.4px;
    margin: 0;
}

.bi {
    margin: 0 !important;
}

.text-nowrap {
    font-size: 14.4px;
    color: rgb(138, 150, 156);
}
</style>
