<script setup>
import {ref, reactive, computed} from 'vue';
import {icon_objects} from '@/main.js';
import {useToast} from "primevue/usetoast";
const toast = useToast();

let new_task_type = reactive({
  name: null,
  icon: null
});

let new_task_types_errors = ref([]);
let task_types = reactive([]);

const icon_objects_normalized = icon_objects.map(function(icon_object) {
  return {
    name: icon_object[1].name,
    style: icon_object[1].style,
    key: icon_object[1].name + '-' + icon_object[1].style
  };
});

function createTaskType()
{
  const task_type_data = {
    type_name: new_task_type.name,
    icon_name: new_task_type.icon?.name ?? null,
    icon_style: new_task_type.icon?.style ?? null
  };

  axios.post('http://localhost:8081/task-types', task_type_data)
      .then((response) => {
        new_task_types_errors.value = [];

        new_task_type.name = null;
        new_task_type.icon = null;

        toast.add({severity: 'success', summary: 'Success', detail: 'Task Type created', life: 3000});
      })
      .catch((error) => {
        toast.add({severity: 'error', summary: 'Error', detail: 'Entered data is invalid - please check the errors', life: 3000});

        new_task_types_errors.value = error.response.data.errors;
      });
}

//Load task types
axios.get('http://localhost:8081/task-types')
    .then((response) => task_types.push(...response.data) );

let userDefinedTaskTypes = computed(() => task_types.filter(task_type => task_type.is_default == false));
let systemTaskTypes = computed(() => task_types.filter(task_type => task_type.is_default == true));

function deleteTaskType(task_type_id)
{
  alert('deleted ' + task_type_id);
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
    <p class="flex items-center gap-3"><unicon :name="taskType.icon_name" :icon-style="taskType.icon_style" height="20" width="20"></unicon> {{ taskType.type_name }}</p>
    <Button class="ml-2 shrink-0 border-0! bg-red-700! hover:bg-red-500! self-stretch" @click="() => deleteTaskType(taskType.id)">
      <unicon name="trash" fill="#fff"></unicon>
    </Button>
  </div>

  <div>
    <h2 class="font-bold text-xl mt-5! mb-2!">Create new Task Type</h2>

    <div class="mb-3! flex gap-2">
      <InputText id="task_header" autocomplete="off" v-model="new_task_type.name"
                 placeholder="New Task Type Name" class="flex-auto"/>
      <Button @click="createTaskType">Create</Button>
    </div>
    <p class="text-red-500 mb-2! mt-1!" v-if="new_task_types_errors['type_name']">{{ new_task_types_errors['type_name'][0] }}</p>

    <details open>
      <p class="text-red-500 mb-2!" v-if="new_task_types_errors['icon_name']">{{ new_task_types_errors['icon_name'][0] }}</p>
      <summary><b>Select an icon for new task type</b></summary>

      <div class="icons_grid">
        <SelectButton v-model="new_task_type.icon" :options="icon_objects_normalized" dataKey="key" optionLabel="value">
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
