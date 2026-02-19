<script setup>
import {ref, reactive} from "vue";

import { useToast } from 'primevue/usetoast';
const toast = useToast();

let createSpaceDialogVisible = ref(false);
let createSpaceForm = reactive({
  name: '',
  description: ''
});
const createSpaceErrors = ref({});

function createSpace() {
  axios.post('http://localhost:8081/spaces', createSpaceForm)
      .then((response) => {
        createSpaceErrors.value = {};
        createSpaceDialogVisible.value = false;

        createSpaceForm.name = '';
        createSpaceForm.description = '';

        toast.add({severity:'success', summary: 'Success', detail:'Space created', life: 3000});
      })
      .catch((error) => {
        createSpaceErrors.value = error.response.data.errors;
      });
}

//load spaces
let spaces = reactive([]);
let selectedTreeItem = ref({});

axios.get('http://localhost:8081/spaces')
.then((response) => {
  console.log(response.data);
});

</script>

<template>
<default-layout>
    <!--Floating notification component-->
    <Toast position="top-left"/>

    <!--CREATE SPACE DIALOG-->
    <Dialog v-model:visible="createSpaceDialogVisible" modal header="Create a space" :style="{ width: '25rem' }">
      <div class="flex items-center gap-4 mb-4">
        <label for="space_name" class="font-semibold w-24">Name</label>
        <InputText id="space_name" class="flex-auto" autocomplete="off" v-model="createSpaceForm.name"/>
      </div>
      <p class="text-red-500" v-if="createSpaceErrors.name">{{ createSpaceErrors.name[0] }}</p>
      <div class="flex items-center gap-4 mb-8">
        <label for="space_description" class="font-semibold w-24">Description</label>
        <InputText id="space_description" class="flex-auto" autocomplete="off" v-model="createSpaceForm.description"/>
      </div>
      <div class="flex justify-end gap-2">
        <Button type="button" label="Cancel" severity="secondary" @click="createSpaceDialogVisible = false"></Button>
        <Button type="button" label="Save" @click="createSpace"></Button>
      </div>
    </Dialog>

    <!-- WHITE SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-section">
            <div class="sidebar-title">Home</div>
            <div class="sidebar-item">Inbox</div>
            <div class="sidebar-item">Replies</div>
            <div class="sidebar-item">Assigned Comments</div>
            <div class="sidebar-item">My Tasks</div>
        </div>

        <div class="sidebar-divider"></div>

        <div class="sidebar-section">
            <div class="sidebar-title flex items-center">
              <p class="grow">Spaces</p>
              <Button @click="createSpaceDialogVisible = true"><unicon name="plus" fill="#fff"></unicon></Button>
            </div>
            <Tree v-model:selectionKeys="selectedTreeItem" :value="spaces" selectionMode="single" class="w-full p-0!"></Tree>
        </div>
    </aside>


    <!-- MAIN -->
    <div class="main">

        <!-- TOP NAV -->
        <header class="topbar">
            <div class="top-left">
                <div class="workspace-title">
                    Магазин, + EXPENSE TRACKER
                </div>

                <div class="view-tabs">
                    <div class="tab">Overview</div>
                    <div class="tab active">List</div>
                    <div class="tab">Board</div>
                    <div class="tab">Whiteboard</div>
                    <div class="tab">+ View</div>
                </div>
            </div>

            <div class="top-right">
                <input class="search" placeholder="Search Ctrl K" />
                <button class="btn-task" @click="testAxios">+ Task</button>
            </div>
        </header>


        <!-- CONTENT -->
        <div class="content">

            <!-- SECTION 1 -->
            <div class="section">

                <div class="section-header">
                    <div class="section-title">
                        basic features
                    </div>

                    <div class="status-pill gray">
                        TO DO <span>7</span>
                    </div>
                </div>

                <div class="task-table">
                    <div class="task-row header">
                        <div class="col-name">Name</div>
                        <div class="col-due">Due date</div>
                        <div class="col-priority">Priority</div>
                    </div>

                    <div class="task-row">
                        <div class="col-name">expense tracker features для моего проекта</div>
                        <div class="col-due"></div>
                        <div class="col-priority"></div>
                    </div>

                    <div class="task-row">
                        <div class="col-name">примеров проектов expense tracker</div>
                        <div class="col-due"></div>
                        <div class="col-priority"></div>
                    </div>
                </div>

            </div>


            <!-- SECTION 2 -->
            <div class="section">

                <div class="section-header">
                    <div class="section-title">
                        News
                    </div>

                    <div class="status-pill purple">
                        IN PROGRESS <span>1</span>
                    </div>
                </div>

                <div class="task-table">
                    <div class="task-row header">
                        <div class="col-name">Name</div>
                        <div class="col-due">Due date</div>
                        <div class="col-priority">Priority</div>
                    </div>

                    <div class="task-row">
                        <div class="col-name">в базе еще нужно сохранить ссылки на загруженные изображения</div>
                        <div class="col-due"></div>
                        <div class="col-priority"></div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</default-layout>
</template>

<style scoped></style>
