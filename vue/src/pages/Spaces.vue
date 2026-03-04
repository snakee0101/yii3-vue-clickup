<script setup>
import {ref, reactive, computed, watch} from "vue";
import Priorities from "@/utilities/priority.js";
import {useToast} from 'primevue/usetoast';
import priority from "@/utilities/priority.js";

const toast = useToast();
let all_tags_list = reactive([]);

function createRandomString(length) {
  const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  let result = "";
  for (let i = 0; i < length; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return result;
}


function formatBytes(bytes) {
  if (bytes < 1024) {
    return bytes + ' B';
  }

  if (bytes < 1024 * 1024) {
    return (bytes / 1024).toFixed(1).replace(/\.0$/, '') + ' kB';
  }

  return (bytes / (1024 * 1024)).toFixed(1).replace(/\.0$/, '') + ' MB';
}

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
let selectedTreeItem = ref({"all": true});

function parseTreeItemData(parsedValue) {
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
                  list_id: task.list_id,
                  priority: task.priority,
                  start_date: task.start_date,
                  due_date: task.due_date,
                  tags: task.tags.map(tag => ({
                    'id': tag.id,
                    'tag_name': tag.tag_name
                  }))
                },
                children: task.subtasks.map(subtask => ({
                  key: 'task-' + subtask.id,
                  data: {
                    id: subtask.id,
                    task_header: subtask.task_header,
                    task_content: subtask.task_content,
                    parent_id: subtask.parent_id,
                    list_id: subtask.list_id,
                    priority: subtask.priority,
                    start_date: subtask.start_date,
                    due_date: subtask.due_date,
                    tags: subtask.tags.map(tag => ({
                      'id': tag.id,
                      'tag_name': tag.tag_name
                    }))
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
            data: {type: 'all'},
            children: spaceNodes
          }
        ];

        // 🔥 SET SELECTION AFTER DATA EXISTS
        selectedTreeItem.value = {all: true};

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
  parent_id: null,
  priority: null,
  start_date: null,
  due_date: null,
  tags: [],
  attachments: [],
  checklists: []
});

function openCreateTaskDialog(taskList, parent_id) {
  createTaskDialogVisible.value = true;
  selectedTaskListId.value = taskList.key.split('-')[1];

  createTaskForm.parent_id = parent_id;
}

const createTaskErrors = ref({});

function toLocalDateString(isoString) {
  const date = new Date(isoString);

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
}

function appendIfNotNull(form, field, value)
{
  if(value !== null) {
    form.append(field, value);
  }
}

function createTask() {
  //transform to FormData to be able to send files
  let createTaskFormData = new FormData();
  appendIfNotNull(createTaskFormData, 'task_header', createTaskForm.task_header);
  appendIfNotNull(createTaskFormData, 'task_content', createTaskForm.task_content);
  appendIfNotNull(createTaskFormData, 'list_id', selectedTaskListId.value);
  appendIfNotNull(createTaskFormData, 'parent_id', createTaskForm.parent_id);
  appendIfNotNull(createTaskFormData, 'priority', createTaskForm.priority);
  appendIfNotNull(createTaskFormData, 'start_date', createTaskForm.start_date);
  appendIfNotNull(createTaskFormData, 'due_date', createTaskForm.due_date);
  appendIfNotNull(createTaskFormData, 'tags', JSON.stringify(createTaskForm.tags));
  appendIfNotNull(createTaskFormData, 'checklists', JSON.stringify(createTaskForm.checklists));

  createTaskForm.attachments.forEach((file, index) => {
    createTaskFormData.append('attachments[]', file);
  });

  axios.post('http://localhost:8081/tasks', createTaskFormData)
      .then((response) => {
        createTaskErrors.value = {};
        createTaskDialogVisible.value = false;

        createTaskForm.task_header = '';
        createTaskForm.task_content = '';
        createTaskForm.list_id = null;
        createTaskForm.parent_id = null;
        createTaskForm.priority = null;
        createTaskForm.start_date = null;
        createTaskForm.due_date = null;
        createTaskForm.tags = [];
        createTaskForm.attachments = [];
        createTaskForm.checklists = [];

        toast.add({severity: 'success', summary: 'Success', detail: 'Task created', life: 3000});
        reloadSpaces();

        //reload tags
        axios.get('http://localhost:8081/tags')
            .then((response) => all_tags_list = response.data);
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
      if (folder.key == 'folder-' + folderId) {
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
        if (list.key == 'list-' + listId) {
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
  task_id: null,
  task_header: '',
  task_content: '',
  priority: null,
  start_date: null,
  due_date: null,
  tags: [],
  attachments: [],
  new_attachments: [],
  checklists: []
});

let editTaskDialogVisible = ref(false);
let editTaskErrors = ref([]);

function openEditTaskDialog(task_id) {
  axios.get('http://localhost:8081/tasks/' + task_id)
  .then((response) => {
    editTaskForm.task_id = response.data.id;
    editTaskForm.task_header = response.data.task_header;
    editTaskForm.task_content = response.data.task_content;
    editTaskForm.priority = response.data.priority;
    editTaskForm.start_date = response.data.start_date;
    editTaskForm.due_date = response.data.due_date;
    editTaskForm.tags = response.data.tags;
    editTaskForm.attachments = response.data.attachments;
    editTaskForm.checklists = response.data.checklists;
  })

  editTaskDialogVisible.value = true;
}

function editTask() {
  let editTaskFormData = new FormData();
  appendIfNotNull(editTaskFormData, 'task_header', editTaskForm.task_header);
  appendIfNotNull(editTaskFormData, 'task_content', editTaskForm.task_content);
  appendIfNotNull(editTaskFormData, 'priority', editTaskForm.priority);
  appendIfNotNull(editTaskFormData, 'start_date', editTaskForm.start_date);
  appendIfNotNull(editTaskFormData, 'due_date', editTaskForm.due_date);
  appendIfNotNull(editTaskFormData, 'tags', JSON.stringify(editTaskForm.tags));
  appendIfNotNull(editTaskFormData, 'attachments', JSON.stringify(editTaskForm.attachments));

  editTaskForm.new_attachments.forEach((file, index) => {
    editTaskFormData.append('new_attachments[]', file);
  });

  axios.post('http://localhost:8081/tasks/' + editTaskForm.task_id, editTaskFormData, {
    headers: {
      'X-HTTP-Method-Override': 'PUT'
    }
  })
      .then((response) => {
        editTaskErrors.value = {};
        editTaskDialogVisible.value = false;

        editTaskForm.task_header = '';
        editTaskForm.task_content = '';
        editTaskForm.priority = null;
        editTaskForm.start_date = null;
        editTaskForm.due_date = null;
        editTaskForm.tags = [];
        editTaskForm.attachments = [];
        editTaskForm.new_attachments = [];

        toast.add({severity: 'success', summary: 'Success', detail: 'Task changed', life: 3000});
        reloadSpaces();
      })
      .catch((error) => {
        editTaskErrors.value = error.response.data.errors;
      });
}

//change priority
function updateTaskPriority(task, priority) {
  axios.put('http://localhost:8081/tasks/' + task.id, {
    'task_header': task.task_header,
    'task_content': task.task_content,
    'priority': priority,
    'start_date': task.start_date,
    'due_date': task.due_date,
    'tags': task.tags
  });
}

//update start and due date
function updateStartDate(updated_date, task)
{
  axios.put('http://localhost:8081/tasks/' + task.id, {
    'task_header': task.task_header,
    'task_content': task.task_content,
    'priority': task.priority,
    'start_date': updated_date,
    'due_date': task.due_date,
    'tags': task.tags
  });
}

function updateDueDate(updated_due_date, task)
{
  axios.put('http://localhost:8081/tasks/' + task.id, {
    'task_header': task.task_header,
    'task_content': task.task_content,
    'priority': task.priority,
    'start_date': task.start_date,
    'due_date': updated_due_date,
    'tags': task.tags
  }).catch((error) => {
    toast.add({severity: 'error', summary: 'Error', detail: error.response.data.errors.due_date[0], life: 3000});
    task.due_date = null;
  });
}

function detachTag(task_id, tag_id)
{
  axios.post('http://localhost:8081/tags/detach', {
    'task_id': task_id,
    'tag_id': tag_id
  });
}

//Tags search
let create_task_autocompleted_tag_name = ref('');
let tag_suggestions = reactive([]);

axios.get('http://localhost:8081/tags')
    .then((response) => all_tags_list = response.data);

//Tags processing
function add_tags_to_created_task()
{
  if(createTaskForm.tags.find(t => t.tag_name === create_task_autocompleted_tag_name.value) !== undefined) {
    toast.add({severity: 'error', summary: 'Error', detail: "Can't add duplicate tag", life: 3000});
    return;
  }

  let tag_id = all_tags_list.find(tag => tag.tag_name === create_task_autocompleted_tag_name.value)?.id ?? null;
  createTaskForm.tags.push({'tag_name': create_task_autocompleted_tag_name.value, 'id': tag_id});

  create_task_autocompleted_tag_name.value = '';
}

function add_tags_to_edited_task()
{
  if(editTaskForm.tags.find(t => t.tag_name === create_task_autocompleted_tag_name.value) !== undefined) {
    toast.add({severity: 'error', summary: 'Error', detail: "Can't add duplicate tag", life: 3000});
    return;
  }

  let tag_id = all_tags_list.find(tag => tag.tag_name === create_task_autocompleted_tag_name.value)?.id ?? null;
  editTaskForm.tags.push({'tag_name': create_task_autocompleted_tag_name.value, 'id': tag_id});

  create_task_autocompleted_tag_name.value = '';
}

function search_tags(event)
{
  tag_suggestions = all_tags_list.filter(tag => tag.tag_name.toLowerCase().includes(event.query.toLowerCase()))
      .map(tag => tag.tag_name);
}

//process attachments
function addAttachmentsToNewTask(event)
{
  event.files.forEach(file => createTaskForm.attachments.push(file));
}

function clearAttachmentsFromNewTask()
{
  createTaskForm.attachments = [];
}

function removeAttachmentsFromNewTask($event)
{
  createTaskForm.attachments.splice(createTaskForm.attachments.indexOf($event.file), 1);
}

let edit_task_attachments = reactive([]);

function addAttachmentsToEditedTask(event)
{
  event.files.forEach(file => editTaskForm.new_attachments.push(file));
}

function clearAttachmentsFromEditedTask()
{
  editTaskForm.new_attachments = [];
}

function removeAttachmentsFromEditedTask($event)
{
  editTaskForm.new_attachments.splice(editTaskForm.new_attachments.indexOf($event.file), 1);
}

function deleteAttachment(attachment)
{
  editTaskForm.attachments.splice(editTaskForm.attachments.indexOf(attachment), 1);
}

//create checklists
function createChecklistForNewTask()
{
  createTaskForm.checklists.push({
    'temp_unique_id': createRandomString(64),
    'checklist_name': 'Checklist',
    'task_id': null,
    'items': [{
      'item_name': '',
      'is_completed': false,
      'temp_unique_id': createRandomString(64)
    }]
  });
}

function deleteCreateFormChecklist(temp_unique_id)
{
  const index = createTaskForm.checklists.findIndex(
      checklist => checklist.temp_unique_id === temp_unique_id
  );

  createTaskForm.checklists.splice(index, 1);
}

function deleteTaskFromCreateChecklist(checklist_unique_id, checklist_item_unique_id)
{
  let checklist = createTaskForm.checklists.find(
      checklist => checklist.temp_unique_id === checklist_unique_id
  );

  const item_index = checklist.items.findIndex(check_list_item => check_list_item.temp_unique_id == checklist_item_unique_id);
  checklist.items.splice(item_index, 1);
}

function createTaskForCreateChecklist(checklist_temp_unique_id)
{
  let checklist = createTaskForm.checklists.find(
      checklist => checklist.temp_unique_id === checklist_temp_unique_id
  );

  checklist.items.push({
    'item_name': '',
    'is_completed': false,
    'temp_unique_id': createRandomString(64)
  });
}

watch(selectedTreeItem, processSelectedTreeItem, {immediate: true});
</script>

<template>
  <default-layout>
    <!--Floating notification component-->
    <Toast position="top-left"/>

    <!--CREATE TASK DIALOG-->
    <Dialog v-model:visible="createTaskDialogVisible" modal header="Create a Task" :style="{ width: '70rem' }">
      <div class="flex items-center gap-4 mb-4">
        <InputText id="task_header" class="flex-auto" autocomplete="off" v-model="createTaskForm.task_header"
                   placeholder="Task name"/>
      </div>
      <p class="text-red-500" v-if="createTaskErrors.task_header">{{ createTaskErrors.task_header[0] }}</p>
      <div class="flex items-center gap-4 mt-4!">
        <Textarea id="task_content" class="flex-auto" autocomplete="off" rows="5" cols="30"
                  v-model="createTaskForm.task_content" placeholder="Task description"/>
      </div>
      <div class="flex items-center gap-4 mb-4 mt-4!">
        <p>Priority</p>
        <Select v-model="createTaskForm.priority" :options="Priorities.values" optionLabel="label" optionValue="value"
                class="w-full md:w-56">
          <template #value="slotProps">
            <div v-if="slotProps.value" class="flex items-center">
              <unicon name="tachometer-fast" width="20" height="20"
                      :fill="Priorities.findByValue(slotProps.value).color"></unicon>
              <p class="ml-2!">{{ Priorities.findByValue(slotProps.value).label }}</p>
            </div>
            <span v-else class="flex">
              <unicon name="tachometer-fast" width="20" height="20"
                      :fill="Priorities.findByLabel('Clear').color"></unicon>
              <p class="ml-2!">Clear</p>
            </span>
          </template>
          <template #option="slotProps">
            <div class="flex items-center">
              <unicon name="tachometer-fast" width="20" height="20"
                      :fill="Priorities.findByLabel(slotProps.option.label).color"></unicon>
              <p class="ml-2!">{{ slotProps.option.label }}</p>
            </div>
          </template>
        </Select>
      </div>
      <div class="flex items-center gap-4 mb-4 mt-4!">
        <p>Start Date</p>
        <DatePicker v-model="createTaskForm.start_date" showIcon fluid iconDisplay="input" dateFormat="yy-mm-dd"
                    updateModelType="string"/>
        <p>Due Date</p>
        <DatePicker v-model="createTaskForm.due_date" showIcon fluid iconDisplay="input" dateFormat="yy-mm-dd"
                    updateModelType="string"/>
      </div>
      <div class="mt-4!">
        <p><b>Tags</b></p>
        <div class="flex items-stretch gap-2 my-2!">
          <AutoComplete v-model="create_task_autocompleted_tag_name" :suggestions="tag_suggestions" @complete="search_tags"/>
          <Button type="button" label="Add" @click="add_tags_to_created_task" class="px-4!"></Button>
        </div>
        <div>
          <Chip v-for="new_task_tag in createTaskForm.tags" :key="new_task_tag" :label="new_task_tag.tag_name" removable class="mr-2! mb-2! px-2! py-1!" @remove="() => createTaskForm.tags.splice(createTaskForm.tags.indexOf(new_task_tag), 1)"/>
        </div>
      </div>
      <div class="mt-4!">
        <p><b>Attachments</b></p>
        <p><FileUpload name="new_task_attachments[]" @select="addAttachmentsToNewTask($event)" @clear="clearAttachmentsFromNewTask" @remove="removeAttachmentsFromNewTask($event)" :multiple="true" :maxFileSize="10000000"></FileUpload></p>
      </div>
      <div class="mt-4!">
        <div class="mb-2!"><b>Checklists</b></div>
        <a href="#" @click.prevent="createChecklistForNewTask()" class="text-blue-500 hover:underline hover:text-blue-800">+ Add Checklist</a>

        <div class="border-1 border-gray-300 rounded p-2! mt-3!" v-for="checklist in createTaskForm.checklists" :key="checklist.temp_unique_id">
          <div class="flex gap-2">
            <InputText type="text" class="p-0! border-0! font-bold! grow!" placeholder="enter checklist name..." v-model="checklist.checklist_name"/>
            <a href="#" class="text-red-600 hover:text-red-800 hover:underline" @click.prevent="() => deleteCreateFormChecklist(checklist.temp_unique_id)" :title="checklist.temp_unique_id">Delete checklist</a>
          </div>
          <p class="text-red-500 mb-2!" v-if="createTaskErrors['checklists.' + checklist.temp_unique_id]">{{ createTaskErrors['checklists.' + checklist.temp_unique_id][0] }}</p>
          <div v-for="checklist_item in checklist.items" :key="checklist_item.temp_unique_id">
            <div class="flex items-center gap-1">
              <div class="flex items-center flex-1">
                <Checkbox v-model="checklist_item.is_completed" :inputId="'checklistitem-' + checklist_item.temp_unique_id" :name="'checklistitem-' + checklist_item.temp_unique_id" binary />
                <InputText
                    type="text"
                    class="ml-2! p-0! border-0! flex-1 w-full"
                    v-model="checklist_item.item_name"
                    placeholder="enter item name..."
                />
              </div>

              <Button class="ml-2 shrink-0 border-0! bg-red-700! hover:bg-red-500!" @click="() => deleteTaskFromCreateChecklist(checklist.temp_unique_id, checklist_item.temp_unique_id)">
                <unicon name="trash" fill="#fff"></unicon>
              </Button>
            </div>
            <p class="text-red-500 mb-2!" v-if="createTaskErrors['checklists.' + checklist.temp_unique_id + '.item.' + checklist_item.temp_unique_id]">{{ createTaskErrors['checklists.' + checklist.temp_unique_id + '.item.' + checklist_item.temp_unique_id][0] }}</p>
          </div>
          <div class=" mt-4!"><a href="#" @click.prevent="() => createTaskForCreateChecklist(checklist.temp_unique_id)" class="text-blue-500 hover:underline hover:text-blue-800">+ Add Item</a></div>
        </div>
      </div>
      <p class="text-red-500" v-if="createTaskErrors.due_date">{{ createTaskErrors.due_date[0] }}</p>
      <div class="flex justify-end gap-2 mt-4!">
        <Button type="button" label="Cancel" severity="secondary" @click="createTaskDialogVisible = false"></Button>
        <Button type="button" label="Save" @click="createTask"></Button>
      </div>
    </Dialog>

    <!--EDIT TASK DIALOG-->
    <Dialog v-model:visible="editTaskDialogVisible" modal header="Edit a Task" :style="{ width: '70rem' }">
      <div class="flex items-center gap-4 mb-4">
        <InputText id="edit_task_header" class="flex-auto" autocomplete="off" v-model="editTaskForm.task_header"
                   placeholder="Task name"/>
      </div>
      <p class="text-red-500" v-if="editTaskErrors.task_header">{{ editTaskErrors.task_header[0] }}</p>
      <div class="flex items-center gap-4 mt-4!">
        <Textarea id="edit_task_content" class="flex-auto" autocomplete="off" rows="5" cols="30"
                  v-model="editTaskForm.task_content" placeholder="Task description"/>
      </div>
      <div class="flex items-center gap-4 mb-4 mt-4!">
        <p>Priority</p>
        <Select v-model="editTaskForm.priority" :options="Priorities.values" optionLabel="label" optionValue="value"
                class="w-full md:w-56">
          <template #value="slotProps">
            <div v-if="slotProps.value" class="flex items-center">
              <unicon name="tachometer-fast" width="20" height="20"
                      :fill="Priorities.findByValue(slotProps.value).color"></unicon>
              <p class="ml-2!">{{ Priorities.findByValue(slotProps.value).label }}</p>
            </div>
            <span v-else class="flex">
              <unicon name="tachometer-fast" width="20" height="20"
                      :fill="Priorities.findByLabel('Clear').color"></unicon>
              <p class="ml-2!">Clear</p>
            </span>
          </template>
          <template #option="slotProps">
            <div class="flex items-center">
              <unicon name="tachometer-fast" width="20" height="20"
                      :fill="Priorities.findByLabel(slotProps.option.label).color"></unicon>
              <p class="ml-2!">{{ slotProps.option.label }}</p>
            </div>
          </template>
        </Select>
      </div>
      <div class="flex items-center gap-4 mb-4 mt-4!">
        <p>Start Date</p>
        <DatePicker v-model="editTaskForm.start_date" showIcon fluid iconDisplay="input" dateFormat="yy-mm-dd"
                    updateModelType="string"/>
        <p>Due Date</p>
        <DatePicker v-model="editTaskForm.due_date" showIcon fluid iconDisplay="input" dateFormat="yy-mm-dd"
                    updateModelType="string"/>
      </div>
      <div class="mt-4!">
        <p><b>Tags</b></p>
        <div class="flex items-stretch gap-2 my-2!">
          <AutoComplete v-model="create_task_autocompleted_tag_name" :suggestions="tag_suggestions" @complete="search_tags"/>
          <Button type="button" label="Add" @click="add_tags_to_edited_task" class="px-4!"></Button>
        </div>
        <div>
          <Chip v-for="edited_task_tag in editTaskForm.tags" :key="edited_task_tag.id" :label="edited_task_tag.tag_name" removable class="mr-2! mb-2! px-2! py-1!" @remove="() => editTaskForm.tags.splice(editTaskForm.tags.indexOf(edited_task_tag), 1)"/>
        </div>
      </div>
      <div class="mt-4!">
        <p><b>Attachments</b></p>
        <DataTable :value="editTaskForm.attachments" tableStyle="min-width: 50rem" v-if="editTaskForm.attachments.length > 0">
          <Column field="filename" header="Filename"></Column>
          <Column header="Size (bytes)">
            <template #body="slotProps">
              {{ formatBytes(slotProps.data.size) }}
            </template>
          </Column>
          <Column field="created_at" header="Import date"></Column>
          <Column header="Actions">
            <template #body="slotProps">
              <div class="flex gap-2">
                <a href="#" @click.prevent="() => deleteAttachment(slotProps.data)">
                  <unicon name="trash" fill="#be0000" height="24" width="24"></unicon>
                </a>
                <a :href="'http://localhost:8081/download?attachment_id=' + slotProps.data.id" download target="_blank">
                  <unicon name="download-alt" icon-style="solid" fill="#0048ff" height="24" width="24"></unicon>
                </a>
              </div>
            </template>
          </Column>
        </DataTable>
        <p v-else>
          No attachments yet
        </p>
      </div>
      <div class="mt-4!">
        <p><b>New Attachments</b></p>
        <p><FileUpload name="edit_task_attachments[]" @select="addAttachmentsToEditedTask($event)" @clear="clearAttachmentsFromEditedTask" @remove="removeAttachmentsFromEditedTask($event)" :multiple="true" :maxFileSize="10000000"></FileUpload></p>
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
        <InputText id="edit_folder_description" class="flex-auto" autocomplete="off"
                   v-model="editFolderForm.description"/>
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
        <InputText id="edit_space_description" class="flex-auto" autocomplete="off"
                   v-model="editSpaceForm.description"/>
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
        <Button type="button" label="+ Folder" @click="createFolderDialogVisible = true"
                class="p-0! px-1! mr-2!"></Button>
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
            <Button type="button" label="+ Task" @click="openCreateTaskDialog(taskList, null)"
                    class="p-0! px-1! mr-2!"></Button>
          </div>

          <TreeTable :value="taskList.tasks" tableStyle="min-width: 50rem">
            <Column header="Name" expander style="" key="task.data.id">
              <template #body="slotProps">
                <a href="#" @click.prevent="() => openEditTaskDialog(slotProps.node.data.id)"
                   class="task_link">{{ slotProps.node.data.task_header }}</a>
              </template>
            </Column>
            <Column header="Priority" style="">
              <template #body="slotProps">
                <Select v-model="slotProps.node.data.priority"
                        @change="() => updateTaskPriority(slotProps.node.data, slotProps.node.data.priority)"
                        :options="Priorities.values" optionLabel="label" optionValue="value" class="w-full md:w-56">
                  <template #value="slotProps">
                    <div v-if="slotProps.value" class="flex items-center">
                      <unicon name="tachometer-fast" width="20" height="20"
                              :fill="Priorities.findByValue(slotProps.value).color"></unicon>
                      <p class="ml-2!">{{ Priorities.findByValue(slotProps.value).label }}</p>
                    </div>
                    <span v-else class="flex">
                      <unicon name="tachometer-fast" width="20" height="20"
                              :fill="Priorities.findByLabel('Clear').color"></unicon>
                      <p class="ml-2!">Clear</p>
                    </span>
                  </template>
                  <template #option="slotProps">
                    <div class="flex items-center">
                      <unicon name="tachometer-fast" width="20" height="20"
                              :fill="Priorities.findByLabel(slotProps.option.label).color"></unicon>
                      <p class="ml-2!">{{ slotProps.option.label }}</p>
                    </div>
                  </template>
                </Select>
              </template>
            </Column>
            <Column header="Start Date" style="">
              <template #body="slotProps">
                <DatePicker v-model="slotProps.node.data.start_date" showIcon fluid iconDisplay="input" dateFormat="yy-mm-dd" updateModelType="string" @update:modelValue="(updated_date) => updateStartDate(updated_date, slotProps.node.data)"/>
              </template>
            </Column>
            <Column header="Due Date" style="">
              <template #body="slotProps">
                <DatePicker v-model="slotProps.node.data.due_date" showIcon fluid iconDisplay="input" dateFormat="yy-mm-dd" updateModelType="string" @update:modelValue="(updated_due_date) => updateDueDate(updated_due_date, slotProps.node.data)"/>
              </template>
            </Column>
            <Column header="Tags" style="">
              <template #body="slotProps">
                <div>
                  <Chip v-for="tag in slotProps.node.data.tags" :label="tag.tag_name" removable class="mr-2! mb-2! px-2! py-1!" @remove="detachTag(slotProps.node.data.id, tag.id)"/>
                </div>
              </template>
            </Column>
            <Column header="Actions" style="">
              <template #body="slotProps">
                <Button type="button" label="+ SubTask" @click="openCreateTaskDialog(taskList, slotProps.node.data.id)"
                        class="p-0! px-1! mr-2!" v-if="slotProps.node.data.parent_id == null"></Button>
              </template>
            </Column>
          </TreeTable>
        </div>
      </div>
    </div>
  </default-layout>
</template>

<style scoped></style>
