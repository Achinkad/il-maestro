<script setup>
import { ref, inject, computed, onBeforeMount } from 'vue'
import { useNodeStore } from '../../stores/node.js'

const axiosApi = inject('axiosApi')
const notyf = inject('notyf')

const nodeStore = useNodeStore()

const loadMasterNodes = (() => { nodeStore.loadMasterNodes() })
const masterNodes = computed(() => { return nodeStore.getMasterNodes() })

const masterNode = ref({
    name: null,
    ip_address: null,
    port: null,
    token: null
})

const registerMasterNode = () => {
    let formData = new FormData()

    formData.append('name', masterNode.value.name)
    formData.append('ip_address', masterNode.value.ip_address)
    formData.append('port', masterNode.value.port)
    formData.append('token', masterNode.value.token)

    nodeStore.registerMasterNode(formData)
    Object.entries(masterNode.value).forEach(([i]) => { masterNode.value[i] = null })
}

// Delete master node
const deleteMasterNode = ((masterNode) => { nodeStore.deleteMasterNode(masterNode) })

// Copy bearer token into the clipboard
const copy = ((node) => {
    navigator.clipboard.writeText(node.token)
    notyf.open({type: 'info', message: 'The bearer token is now on your clipboard. Go paste it!'})
})

const downloadScript = (() => {
    axiosApi.get('nodes/download-script').then((response) => {
        let fileURL = window.URL.createObjectURL(new Blob([response.data]))
        let fileLink = document.createElement('a')

        fileLink.href = fileURL
        fileLink.setAttribute('download', 'maestro')
        document.body.appendChild(fileLink)
        fileLink.click()

        notyf.success('The script was downloaded with success.')
    }).catch((error) => {
        notyf.error('Oops, an error has occurred.')
    })
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
                <h2 class="p-title">Master Nodes</h2>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="row">
                <div class="col-12">
                    <div class="card card-h-100">
                        <div class="d-flex card-header justify-content-between align-items-center">
                            <h4 class="header-title">Registered Master Nodes</h4>
                        </div>
                        <div class="card-body pt-0">
                            <table class="table table-responsive align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:8%">#ID</th>
                                        <th style="width:18%">Node name</th>
                                        <th style="width:18%">IP address</th>
                                        <th style="width:12%">Port</th>
                                        <th>Bearer token</th>
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
                                        <td style="white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:1px; cursor:pointer;"
                                            @click="copy(node)" title="Click to copy to your clipboard!"><u>{{ node.token }}</u></td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <button class="btn btn-xs btn-light table-button" title="Delete master node" @click="deleteMasterNode(node)">
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
                    <h4 class="header-title">Register a new master node</h4>
                </div>
                <div class="card-body pt-0">
                    <form class="row g-3 needs-validation" @submit.prevent="registerMasterNode">
                        <div class="col-4">
                            <label for="name" class="form-label">Node name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" placeholder="Enter a name"
                            v-model="masterNode.name" required>
                        </div>
                        <div class="col-4">
                            <label for="ip_address" class="form-label">IP address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ip_address" placeholder="Enter an IP address"
                            pattern="^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$"
                            v-model="masterNode.ip_address" required>
                        </div>
                        <div class="col-4">
                            <label for="port" class="form-label">Port <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="port" placeholder="Enter a port"
                            v-model="masterNode.port" required>
                        </div>
                        <div class="col-12">
                            <label for="token" class="form-label">Bearer token <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="token"
                            placeholder="Enter a bearer token" v-model="masterNode.token" required>
                        </div>
                        <div class="col-12 mt-4 d-flex justify-content-end">
                            <div class="px-1">
                                <button type="reset" class="btn btn-light px-4 me-1">Clear</button>
                            </div>
                            <div class="px-1">
                                <button type="submit" class="btn btn-primary">Register master node</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="callout">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> If you do not possess a bearer
                token or wish to obtain the one you already got, you can <span @click="downloadScript()"
                style="cursor:pointer; text-decoration:underline;">click here</span> to download the
                script and execute it on your cluster's master node for token retrieval. This script
                also enables you to authorize access to the REST API by granting permissions. Alternative
                you can download from the latest realease of
                <a href="https://github.com/joseareia/maestro/archive/refs/tags/v1.1.1.zip"
                target="_blank" style="color:#664d03;">Maestro</a> with <code>wget</code> or your favourite tool.
            </div>
        </div>
    </div>
</template>
