<script setup>
import {ref, reactive, computed} from 'vue';
import {icon_objects} from '@/main.js';
import {useToast} from "primevue/usetoast";

const toast = useToast();

let new_task_type = reactive({
  name: null
});

let current_task_type_icon = ref(null);
let new_task_types_errors = ref([]);
let task_types = ref([]);

/* NEW */
let formMode = ref('create'); // create | edit
let editingTaskTypeId = ref(null);

const icon_objects_normalized = icon_objects.map(function(icon_object) {
  return {
    name: icon_object[1].name,
    style: icon_object[1].style,
    key: icon_object[1].name + '-' + icon_object[1].style
  };
});

function resetForm()
{
  new_task_type.name = null;
  current_task_type_icon.value = null;
  new_task_types_errors.value = [];

  formMode.value = 'create';
  editingTaskTypeId.value = null;
}

/* CREATE */

function createTaskType()
{
  const task_type_data = {
    type_name: new_task_type.name,
    icon_name: current_task_type_icon?.value?.name ?? null,
    icon_style: current_task_type_icon?.value?.style ?? null
  };

  axios.post('http://localhost:8081/task-types', task_type_data)
      .then((response) => {

        //full reload, since its a ref
        axios.get('http://localhost:8081/task-types')
            .then((r) => task_types.value = r.data);

        resetForm();

        toast.add({severity: 'success', summary: 'Success', detail: 'Task Type created', life: 3000});
      })
      .catch((error) => {

        toast.add({severity: 'error', summary: 'Error', detail: 'Entered data is invalid - please check the errors', life: 3000});
        new_task_types_errors.value = error.response.data.errors;

      });
}

/* EDIT */

function startEditTaskType(taskType)
{
  formMode.value = 'edit';
  editingTaskTypeId.value = taskType.id;

  new_task_type.name = taskType.type_name;

  current_task_type_icon.value = {
    name: taskType.icon_name,
    style: taskType.icon_style,
    key: taskType.icon_name + '-' + taskType.icon_style
  };
}

function saveEditTaskType()
{
  const task_type_data = {
    type_name: new_task_type.name,
    icon_name: current_task_type_icon?.value?.name ?? null,
    icon_style: current_task_type_icon?.value?.style ?? null
  };

  axios.post('http://localhost:8081/task-types/' + editingTaskTypeId.value, task_type_data,  {
    headers: {
      'X-HTTP-Method-Override': 'PUT'
    }
  }).then((response) => {
        //full reload, since its a ref
        axios.get('http://localhost:8081/task-types')
            .then((r) => task_types.value = r.data);

        resetForm();

        toast.add({severity: 'success', summary: 'Success', detail: 'Task Type edited', life: 3000});
      })
      .catch((error) => {
        toast.add({severity: 'error', summary: 'Error', detail: 'Entered data is invalid - please check the errors', life: 3000});
        new_task_types_errors.value = error.response.data.errors;
      });
}

function cancelEditTaskType()
{
  resetForm();
}


/* LOAD */

axios.get('http://localhost:8081/task-types')
    .then((response) => task_types.value = response.data);

let userDefinedTaskTypes = computed(() =>
    task_types.value.filter(task_type => task_type.is_default == false)
);

let systemTaskTypes = computed(() =>
    task_types.value.filter(task_type => task_type.is_default == true)
);

function deleteTaskType(task_type_id)
{
  axios.delete('http://localhost:8081/task-types/' + task_type_id)
      .then(() => {

        const index = task_types.value.findIndex(t => t.id === task_type_id);
        task_types.value.splice(index, 1);

        toast.add({severity: 'success', summary: 'Success', detail: 'Task Type was deleted', life: 3000});
      });
}
</script>

<template>
<settings-layout>
  <Toast position="top-left"/>

  <h2 class="font-bold text-xl mb-2!">System Task Types</h2>

  <div v-for="taskType in systemTaskTypes" :key="taskType.id" class="bg-white flex mb-3! p-2! justify-between items-center">
    <p class="flex items-center gap-3"><unicon :name="taskType.icon_name" :icon-style="taskType.icon_style" height="20" width="20"></unicon> {{ taskType.type_name }}</p>
  </div>

  <h2 class="font-bold text-xl mb-2! mt-4!">User-Defined Task Types</h2>

  <div v-for="taskType in userDefinedTaskTypes" :key="taskType.id" class="bg-white flex mb-3! p-2! justify-between items-center">

    <p class="flex items-center gap-3">
      <unicon :name="taskType.icon_name" :icon-style="taskType.icon_style" height="20" width="20"></unicon>
      {{ taskType.type_name }}
    </p>

    <div class="flex">
      <Button
          class="ml-2 shrink-0"
          @click="startEditTaskType(taskType)"
      >
        <unicon name="edit"></unicon>
      </Button>
      <Button
          class="ml-2 shrink-0 border-0! bg-red-700! hover:bg-red-500! self-stretch"
          @click="deleteTaskType(taskType.id)"
      >
        <unicon name="trash" fill="#fff"></unicon>
      </Button>

    </div>

  </div>
  <p class="italic" v-if="userDefinedTaskTypes.length == 0">
    No user-defined task types yet
  </p>

  <div>
    <h2 class="font-bold text-xl mt-5! mb-2!">
      {{ formMode === 'create' ? 'Create new Task Type' : 'Edit Task Type' }}
    </h2>

    <div class="mb-3! flex gap-2">
      <InputText
          autocomplete="off"
          v-model="new_task_type.name"
          placeholder="Task Type Name"
          class="flex-auto"
      />

      <!-- CREATE -->
      <Button v-if="formMode === 'create'" @click="createTaskType">
        Create
      </Button>

      <!-- EDIT -->
      <Button v-if="formMode === 'edit'" @click="saveEditTaskType">
        Save
      </Button>

      <Button
          v-if="formMode === 'edit'"
          severity="secondary"
          @click="cancelEditTaskType"
      >
        Cancel
      </Button>

    </div>
    <p class="text-red-500 mb-2! mt-1!" v-if="new_task_types_errors['type_name']">{{ new_task_types_errors['type_name'][0] }}</p>

    <details open>
      <p class="text-red-500 mb-2!" v-if="new_task_types_errors['icon_name']">{{ new_task_types_errors['icon_name'][0] }}</p>
      <summary><b>Select an icon for new task type</b></summary>

      <div class="icons_grid">
        <SelectButton v-model="current_task_type_icon" :options="icon_objects_normalized" dataKey="key" optionLabel="value">
          <template #option="slotProps">
            <unicon :name="slotProps.option.name" width="20" height="20" fill="#000" :icon-style="slotProps.option.style"></unicon>
          </template>
        </SelectButton>
      </div>
    </details>
  </div>
</settings-layout>
</template>

<style scoped></style>
