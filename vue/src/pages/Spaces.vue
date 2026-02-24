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


let createListDialogVisible = ref(false);
let createListForm = reactive({
  list_name: '',
  description: '',
  folder_id: null
});
const createListErrors = ref({});


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
          data: {
            type: 'space',
            description: space.description,
            name: space.space_name
          },
          children: space.folders.map((folder) => ({
            key: 'folder-' + folder.id,
            label: folder.folder_name,
            icon: 'pi pi-folder-open',
            data: {
              type: 'folder',
              folder_name: folder.folder_name,
              description: folder.description
            },
            children: folder.lists.map((list) => ({
              key: 'list-' + list.id,
              label: list.list_name,
              icon: 'pi pi-list',
              data: {
                type: 'list',
                list_name: list.list_name,
                description: list.description
              },
              tasks: list.tasks.map(task => ({
                key: 'task-' + task.id,
                data: {
                  id: task.id,
                  task_header: task.task_header,
                  task_content: task.task_content,
                  parent_id: task.parent_id,
                  list_id: task.list_id
                },
                children: task.subtasks.map(subtask => ({
                  key: 'task-' + subtask.id,
                  data: {
                    id: subtask.id,
                    task_header: subtask.task_header,
                    task_content: subtask.task_content,
                    parent_id: subtask.parent_id,
                    list_id: subtask.list_id
                  }
                }))
              }))
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

function createList() {
  axios.post('http://localhost:8081/task-lists', {...createListForm, folder_id: selectedTreeItemData.value.id})
      .then((response) => {
        createListErrors.value = {};
        createListDialogVisible.value = false;

        createListForm.list_name = '';
        createListForm.description = '';
        createListForm.folder_id = null;

        toast.add({severity: 'success', summary: 'Success', detail: 'List created', life: 3000});
        reloadSpaces();
      })
      .catch((error) => {
        createListErrors.value = error.response.data.errors;
      });
}

const createTaskDialogVisible = ref(false);
const selectedTaskListId = ref(null);

let createTaskForm = reactive({
  task_header: '',
  task_content: '',
  list_id: null,
  parent_id: null
});

function openCreateTaskDialog(taskList, parent_id) {
  createTaskDialogVisible.value = true;
  selectedTaskListId.value = taskList.key.split('-')[1];

  createTaskForm.parent_id = parent_id;
}

const createTaskErrors = ref({});

function createTask() {
  axios.post('http://localhost:8081/tasks', {...createTaskForm, list_id: selectedTaskListId.value})
      .then((response) => {
        createTaskErrors.value = {};
        createTaskDialogVisible.value = false;

        createTaskForm.task_header = '';
        createTaskForm.task_content = '';
        createTaskForm.list_id = null;
        createTaskForm.parent_id = null;

        toast.add({severity: 'success', summary: 'Success', detail: 'Task created', life: 3000});
        reloadSpaces();
      })
      .catch((error) => {
        createTaskErrors.value = error.response.data.errors;
      });
}

//Edit Space Dialog
const editSpaceDialogVisible = ref(false);
let editSpaceForm = reactive({
  name: '',
  description: ''
});
const editSpaceErrors = ref({});

function openEditSpaceDialog() {
  const spaceId = selectedTreeItemData.value.id;

  const allSpaces = spaces.value[0].children;
  const selectedSpace = allSpaces.find(space => space.key == 'space-' + spaceId);

  editSpaceForm.name = selectedSpace.data.name;
  editSpaceForm.description = selectedSpace.data.description;

  editSpaceDialogVisible.value = true;
}

function editSpace() {
  const selectedSpaceId = selectedTreeItemData.value.id;

  axios.put('http://localhost:8081/spaces/' + selectedSpaceId, editSpaceForm)
      .then((response) => {
        editSpaceErrors.value = {};
        editSpaceDialogVisible.value = false;

        editSpaceForm.name = '';
        editSpaceForm.description = '';

        toast.add({severity: 'success', summary: 'Success', detail: 'Space changed', life: 3000});
        reloadSpaces();
      })
      .catch((error) => {
        editSpaceErrors.value = error.response.data.errors;
      });
}


//Edit Folder Dialog
const editFolderDialogVisible = ref(false);
let editFolderForm = reactive({
  folder_name: '',
  description: ''
});
const editFolderErrors = ref({});

function openEditFolderDialog() {
  const folderId = selectedTreeItemData.value.id;

  const allSpaces = spaces.value[0].children;
  allSpaces.map(function (space) {
      //find specific folder and get its fields
      space.children.map(function (folder) {
        if(folder.key == 'folder-' + folderId) {
            editFolderForm.folder_name = folder.data.folder_name;
            editFolderForm.description = folder.data.description;
        }
      });
  });

  editFolderDialogVisible.value = true;
}

function editFolder() {
  const folderId = selectedTreeItemData.value.id;

  axios.put('http://localhost:8081/folders/' + folderId, editFolderForm)
      .then((response) => {
        editFolderErrors.value = {};
        editFolderDialogVisible.value = false;

        editFolderForm.folder_name = '';
        editFolderForm.description = '';

        toast.add({severity: 'success', summary: 'Success', detail: 'Folder changed', life: 3000});
        reloadSpaces();
      })
      .catch((error) => {
        editFolderErrors.value = error.response.data.errors;
      });
}

//Edit List Dialog
const editListDialogVisible = ref(false);
let editListForm = reactive({
  list_name: '',
  description: '',
  folder_id: null
});
const editListErrors = ref({});

function openEditListDialog() {
  const listId = selectedTreeItemData.value.id;

  const allSpaces = spaces.value[0].children;
  allSpaces.map(function (space) {
    //find specific folder and get its fields
    space.children.map(function (folder) {
      folder.children.map(function (list) {
        if(list.key == 'list-' + listId) {
          editListForm.list_name = list.data.list_name;
          editListForm.description = list.data.description;
        }
      });

    });
  });

  editListDialogVisible.value = true;
}

function editList() {
  const listId = selectedTreeItemData.value.id;

  axios.put('http://localhost:8081/task-lists/' + listId, editListForm)
      .then((response) => {
        editListErrors.value = {};
        editListDialogVisible.value = false;

        editListForm.list_name = '';
        editListForm.description = '';

        toast.add({severity: 'success', summary: 'Success', detail: 'List changed', life: 3000});
        reloadSpaces();
      })
      .catch((error) => {
        editListErrors.value = error.response.data.errors;
      });
}

//Edit task dialog
let editTaskForm = reactive({
  task_header: '',
  task_content: '',
  task_id: null
});

let editTaskDialogVisible = ref(false);
let editTaskErrors = ref([]);

function openEditTaskDialog(task_id) {
  const allSpaces = spaces.value[0].children;
  allSpaces.map(function (space) {
    space.children?.map(function (folder) {
      folder.children?.map(function (list) {
        list.tasks?.map(function (task) {
          //search for normal tasks
          if(task.data.id == task_id) {
            editTaskForm.task_header = task.data.task_header;
            editTaskForm.task_content = task.data.task_content;
            editTaskForm.task_id = task_id;
          }

          //search for subtasks
          task.children?.map(function (subtask) {
            if(subtask.data.id == task_id) {
              editTaskForm.task_header = subtask.data.task_header;
              editTaskForm.task_content = subtask.data.task_content;
              editTaskForm.task_id = task_id;
            }
          });
        })
      });

    });
  });

  editTaskDialogVisible.value = true;
}

function editTask() {
  axios.put('http://localhost:8081/tasks/' + editTaskForm.task_id, editTaskForm)
      .then((response) => {
        editTaskErrors.value = {};
        editTaskDialogVisible.value = false;

        editTaskForm.task_header = '';
        editTaskForm.task_content = '';

        toast.add({severity: 'success', summary: 'Success', detail: 'Task changed', life: 3000});
        reloadSpaces();
      })
      .catch((error) => {
        editTaskErrors.value = error.response.data.errors;
      });
}

watch(selectedTreeItem, processSelectedTreeItem, { immediate: true });
</script>

<template>
  <default-layout>
    <!--Floating notification component-->
    <Toast position="top-left"/>

    <!--CREATE TASK DIALOG-->
    <Dialog v-model:visible="createTaskDialogVisible" modal header="Create a Task" :style="{ width: '50rem' }">
      <div class="flex items-center gap-4 mb-4">
        <InputText id="task_header" class="flex-auto" autocomplete="off" v-model="createTaskForm.task_header" placeholder="Task name"/>
      </div>
      <p class="text-red-500" v-if="createTaskErrors.task_header">{{ createTaskErrors.task_header[0] }}</p>
      <div class="flex items-center gap-4 mt-4!">
        <Textarea id="task_content" class="flex-auto" autocomplete="off" rows="5" cols="30" v-model="createTaskForm.task_content" placeholder="Task description"/>
      </div>
      <div class="flex justify-end gap-2 mt-4!">
        <Button type="button" label="Cancel" severity="secondary" @click="createTaskDialogVisible = false"></Button>
        <Button type="button" label="Save" @click="createTask"></Button>
      </div>
    </Dialog>

    <!--EDIT TASK DIALOG-->
    <Dialog v-model:visible="editTaskDialogVisible" modal header="Edit a Task" :style="{ width: '50rem' }">
      <div class="flex items-center gap-4 mb-4">
        <InputText id="edit_task_header" class="flex-auto" autocomplete="off" v-model="editTaskForm.task_header" placeholder="Task name"/>
      </div>
      <p class="text-red-500" v-if="editTaskErrors.task_header">{{ editTaskErrors.task_header[0] }}</p>
      <div class="flex items-center gap-4 mt-4!">
        <Textarea id="edit_task_content" class="flex-auto" autocomplete="off" rows="5" cols="30" v-model="editTaskForm.task_content" placeholder="Task description"/>
      </div>
      <div class="flex justify-end gap-2 mt-4!">
        <Button type="button" label="Cancel" severity="secondary" @click="editTaskDialogVisible = false"></Button>
        <Button type="button" label="Save" @click="editTask"></Button>
      </div>
    </Dialog>

    <!--CREATE LIST DIALOG-->
    <Dialog v-model:visible="createListDialogVisible" modal header="Create a List" :style="{ width: '25rem' }">
      <div class="flex items-center gap-4 mb-4">
        <label for="list_name" class="font-semibold w-24">Name</label>
        <InputText id="list_name" class="flex-auto" autocomplete="off" v-model="createListForm.list_name"/>
      </div>
      <p class="text-red-500" v-if="createListErrors.list_name">{{ createListErrors.list_name[0] }}</p>
      <div class="flex items-center gap-4 mb-8">
        <label for="list_description" class="font-semibold w-24">Description</label>
        <InputText id="list_description" class="flex-auto" autocomplete="off" v-model="createListForm.description"/>
      </div>
      <div class="flex justify-end gap-2">
        <Button type="button" label="Cancel" severity="secondary" @click="createListDialogVisible = false"></Button>
        <Button type="button" label="Save" @click="createList"></Button>
      </div>
    </Dialog>

    <!--EDIT LIST DIALOG-->
    <Dialog v-model:visible="editListDialogVisible" modal header="Edit a List" :style="{ width: '25rem' }">
      <div class="flex items-center gap-4 mb-4">
        <label for="edit_list_name" class="font-semibold w-24">Name</label>
        <InputText id="edit_list_name" class="flex-auto" autocomplete="off" v-model="editListForm.list_name"/>
      </div>
      <p class="text-red-500" v-if="editListErrors.list_name">{{ editListErrors.list_name[0] }}</p>
      <div class="flex items-center gap-4 mb-8">
        <label for="edit_list_description" class="font-semibold w-24">Description</label>
        <InputText id="edit_list_description" class="flex-auto" autocomplete="off" v-model="editListForm.description"/>
      </div>
      <div class="flex justify-end gap-2">
        <Button type="button" label="Cancel" severity="secondary" @click="editListDialogVisible = false"></Button>
        <Button type="button" label="Save" @click="editList"></Button>
      </div>
    </Dialog>

    <!--EDIT FOLDER DIALOG-->
    <Dialog v-model:visible="editFolderDialogVisible" modal header="Edit a folder" :style="{ width: '25rem' }">
      <div class="flex items-center gap-4 mb-4">
        <label for="edit_folder_name" class="font-semibold w-24">Name</label>
        <InputText id="edit_folder_name" class="flex-auto" autocomplete="off" v-model="editFolderForm.folder_name"/>
      </div>
      <p class="text-red-500" v-if="editFolderErrors.folder_name">{{ editFolderErrors.folder_name[0] }}</p>
      <div class="flex items-center gap-4 mb-8">
        <label for="edit_folder_description" class="font-semibold w-24">Description</label>
        <InputText id="edit_folder_description" class="flex-auto" autocomplete="off" v-model="editFolderForm.description"/>
      </div>
      <div class="flex justify-end gap-2">
        <Button type="button" label="Cancel" severity="secondary" @click="editFolderDialogVisible = false"></Button>
        <Button type="button" label="Save" @click="editFolder"></Button>
      </div>
    </Dialog>

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


    <!--EDIT SPACE DIALOG-->
    <Dialog v-model:visible="editSpaceDialogVisible" modal header="Edit a space" :style="{ width: '25rem' }">
      <div class="flex items-center gap-4 mb-4">
        <label for="edit_space_name" class="font-semibold w-24">Name</label>
        <InputText id="edit_space_name" class="flex-auto" autocomplete="off" v-model="editSpaceForm.name"/>
      </div>
      <p class="text-red-500" v-if="editSpaceErrors.space_name">{{ editSpaceErrors.space_name[0] }}</p>
      <div class="flex items-center gap-4 mb-8">
        <label for="edit_space_description" class="font-semibold w-24">Description</label>
        <InputText id="edit_space_description" class="flex-auto" autocomplete="off" v-model="editSpaceForm.description"/>
      </div>
      <div class="flex justify-end gap-2">
        <Button type="button" label="Cancel" severity="secondary" @click="editSpaceDialogVisible = false"></Button>
        <Button type="button" label="Save" @click="editSpace"></Button>
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
      <div class="sidebar-section mt-4!" v-if="selectedTreeItemData.type == 'space'">
        <Button type="button" label="+ Folder" @click="createFolderDialogVisible = true" class="p-0! px-1! mr-2!"></Button>
        <Button type="button" label="Edit" @click="openEditSpaceDialog" class="p-0! px-1! mr-2!"></Button>
      </div>

      <!--Folder Actions-->
      <div class="sidebar-section mt-4!" v-if="selectedTreeItemData.type == 'folder'">
        <Button type="button" label="+ List" @click="createListDialogVisible = true" class="p-0! px-1! mr-2!"></Button>
        <Button type="button" label="Edit" @click="openEditFolderDialog" class="p-0! px-1! mr-2!"></Button>
      </div>

      <!--List Actions-->
      <div class="sidebar-section mt-4!" v-if="selectedTreeItemData.type == 'list'">
        <Button type="button" label="Edit" @click="openEditListDialog" class="p-0! px-1! mr-2!"></Button>
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
            <Button type="button" label="+ Task" @click="openCreateTaskDialog(taskList, null)" class="p-0! px-1! mr-2!"></Button>
          </div>

          <TreeTable :value="taskList.tasks" tableStyle="min-width: 50rem">
            <Column header="Name" expander style="width: 80%" key="task.data.id">
              <template #body="slotProps">
                <a href="#" @click.prevent="() => openEditTaskDialog(slotProps.node.data.id)" class="task_link">{{ slotProps.node.data.task_header }}</a>
              </template>
            </Column>
            <Column header="Actions" style="width: 20%">
              <template #body="slotProps">
                <Button type="button" label="+ SubTask" @click="openCreateTaskDialog(taskList, slotProps.node.data.id)" class="p-0! px-1! mr-2!" v-if="slotProps.node.data.parent_id == null"></Button>
              </template>
            </Column>
          </TreeTable>
        </div>
      </div>
    </div>
  </default-layout>
</template>

<style scoped></style>
