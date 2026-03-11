<script setup>
import {ref, reactive} from 'vue';
import {icon_objects} from '@/main.js';

let new_task_type = reactive({
  name: null,
  icon: null
});

let new_task_types_errors = ref([]);

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
        new_task_types_errors.value = error.response.data.errors;
      });
}
</script>

<template>
<settings-layout>
  <div>
    this is a content of a TASK TYPES settings page using slots
  </div>
  <div>
    there will be icons shown
  </div>
  <div>

    <h2 class="font-bold text-xl mt-5! mb-2!">Create new task type</h2>

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
  <p>{{ new_task_type }}</p>
</settings-layout>
</template>

<style scoped></style>
