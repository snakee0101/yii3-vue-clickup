<script setup>
import {ref, reactive, computed, watch} from "vue";

import {useToast} from 'primevue/usetoast';

const toast = useToast();

let createSpaceDialogVisible = ref(false);
let createSpaceForm = reactive({
  name: '',
  description: ''
});
const createSpaceErrors = ref({});

let createFolderDialogVisible = ref(false);
let createFolderForm = reactive({
  folder_name: '',
  description: '',
  space_id: null
});
const createFolderErrors = ref({});

function createSpace() {
  axios.post('http://localhost:8081/spaces', createSpaceForm)
      .then((response) => {
        createSpaceErrors.value = {};
        createSpaceDialogVisible.value = false;

        createSpaceForm.name = '';
        createSpaceForm.description = '';

        toast.add({severity: 'success', summary: 'Success', detail: 'Space created', life: 3000});
        reloadSpaces();
      })
      .catch((error) => {
        createSpaceErrors.value = error.response.data.errors;
      });
}

//reload spaces
const spaces = ref([]);
let selectedTreeItem = ref({ "all": true });

function parseTreeItemData(parsedValue)
{
  let object_key = Object.keys(parsedValue)[0]; //like "space-1", "folder-2", "list-3", "all". Example: "space-1" -> {type: 'space', id: 1}

  return {
    'type': object_key === 'all' ? 'all' : object_key.split('-')[0],
    'id': object_key === 'all' ? 'all' : object_key.split('-')[1]
  };
}

const selectedTreeItemData = computed(() => parseTreeItemData(selectedTreeItem.value));

function reloadSpaces() {
  axios.get('http://localhost:8081/spaces')
      .then((response) => {

        const spaceNodes = response.data.spaces.map((space) => ({
          key: 'space-' + space.id,
          label: space.space_name,
          icon: 'pi pi-globe',
          data: { type: 'space' },
          children: space.folders.map((folder) => ({
            key: 'folder-' + folder.id,
            label: folder.folder_name,
            icon: 'pi pi-folder-open',
            data: { type: 'folder' },
            children: folder.lists.map((list) => ({
              key: 'list-' + list.id,
              label: list.list_name,
              icon: 'pi pi-list',
              data: { type: 'list' },
              tasks: list.tasks
            }))
          }))
        }));

        spaces.value = [
          {
            key: 'all',
            label: 'All Tasks',
            icon: 'pi pi-th-large',
            data: { type: 'all' },
            children: spaceNodes
          }
        ];

        // 🔥 SET SELECTION AFTER DATA EXISTS
        selectedTreeItem.value = { all: true };

        // optionally process lists here instead of manual call below
        processSelectedTreeItem(selectedTreeItem.value);
      });
}

reloadSpaces();

//extract and flatten lists that belong to the selected item (be it space, folder or list)
const selectedLists = ref([]);

function processSelectedTreeItem(selectedItem) {
  let lists = [];
  const selectedObjectKey = Object.keys(selectedItem)[0];

  if (!selectedObjectKey) {
    selectedLists.value = [];
    return;
  }

  // 👇 actual spaces are inside root
  const realSpaces = spaces.value[0]?.children || [];

  // 🔥 ALL TASKS
  if (selectedObjectKey === 'all') {
    realSpaces.forEach(space =>
        space.children.forEach(folder =>
            folder.children.forEach(list => lists.push(list))
        )
    );

    selectedLists.value = lists;
    return;
  }

  realSpaces.forEach(space => {
    if (selectedObjectKey.startsWith('space-') && selectedObjectKey !== space.key) {
      return;
    }

    space.children.forEach(folder => {
      if (selectedObjectKey.startsWith('folder-') && selectedObjectKey !== folder.key) {
        return;
      }

      folder.children.forEach(list => {
        if (selectedObjectKey.startsWith('list-') && selectedObjectKey !== list.key) {
          return;
        }
        lists.push(list);
      });
    });
  });

  selectedLists.value = lists;
}

function createFolder() {
  axios.post('http://localhost:8081/folders', {...createFolderForm, space_id: selectedTreeItemData.value.id})
      .then((response) => {
        createFolderErrors.value = {};
        createFolderDialogVisible.value = false;

        createFolderForm.folder_name = '';
        createFolderForm.description = '';
        createFolderForm.space_id = null;

        toast.add({severity: 'success', summary: 'Success', detail: 'Folder created', life: 3000});
        reloadSpaces();
      })
      .catch((error) => {
        createFolderErrors.value = error.response.data.errors;
      });
}

watch(selectedTreeItem, processSelectedTreeItem, { immediate: true });
</script>

<template>
  <default-layout>
    <!--Floating notification component-->
    <Toast position="top-left"/>

    <!--CREATE FOLDER DIALOG-->
    <Dialog v-model:visible="createFolderDialogVisible" modal header="Create a folder" :style="{ width: '25rem' }">
      <div class="flex items-center gap-4 mb-4">
        <label for="folder_name" class="font-semibold w-24">Name</label>
        <InputText id="folder_name" class="flex-auto" autocomplete="off" v-model="createFolderForm.folder_name"/>
      </div>
      <p class="text-red-500" v-if="createFolderErrors.folder_name">{{ createFolderErrors.folder_name[0] }}</p>
      <div class="flex items-center gap-4 mb-8">
        <label for="folder_description" class="font-semibold w-24">Description</label>
        <InputText id="folder_description" class="flex-auto" autocomplete="off" v-model="createFolderForm.description"/>
      </div>
      <div class="flex justify-end gap-2">
        <Button type="button" label="Cancel" severity="secondary" @click="createFolderDialogVisible = false"></Button>
        <Button type="button" label="Save" @click="createFolder"></Button>
      </div>
    </Dialog>


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
        <div class="sidebar-item">Menu items</div>
      </div>

      <div class="sidebar-divider"></div>

      <div class="sidebar-section">
        <div class="sidebar-title flex items-center">
          <p class="grow">Spaces</p>
          <Button @click="createSpaceDialogVisible = true">
            <unicon name="plus" fill="#fff"></unicon>
          </Button>
        </div>
        <Tree v-model:selectionKeys="selectedTreeItem" :value="spaces" selectionMode="single"
              class="w-full p-0!"></Tree>
      </div>

      <!--Actions-->
      <!--Spaces Actions-->
      <div class="sidebar-section" v-if="selectedTreeItemData.type == 'space'">
        <Button type="button" label="+ Folder" @click="createFolderDialogVisible = true" class="p-0! px-1! mr-2!"></Button>
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
          <input class="search" placeholder="Search Ctrl K"/>
          <button class="btn-task" @click="testAxios">+ Task</button>
        </div>
      </header>


      <!-- CONTENT -->
      <div class="content">
        <!-- SHOW SELECT LISTS -->
        <div class="section" v-for="taskList in selectedLists">
          <div class="section-header">
            <div class="section-title">
              {{ taskList.label }}
            </div>

            <div class="status-pill gray">
              CUSTOM SEGREGATION BY STATUS (LATER) <span>7</span>
            </div>
          </div>

          <div class="task-table">
            <div class="task-row header">
              <div class="col-name">Name</div>
              <div class="col-due">Due date</div>
              <div class="col-priority">Priority</div>
            </div>

            <div class="task-row" v-for="task in taskList.tasks" :key="task.id">
              <div class="col-name">{{ task.task_header }}</div>
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
