<script setup>
import {ref, reactive} from 'vue';
import {icon_objects} from '@/main.js';

let new_task_status = reactive({
  name: '',
  icon: ''
});

const icon_objects_normalized = icon_objects.map(function(icon_object) {
  return {
    name: icon_object[1].name,
    style: icon_object[1].style,
    key: icon_object[1].name + '-' + icon_object[1].style
  };
});
</script>

<template>
<settings-layout>
  <div>
    this is a content of a TASK STATUSES settings page using slots
  </div>
  <div>
    there will be icons shown
  </div>
  <div>

    <h2 class="font-bold text-xl mt-5! mb-2!">Create new task status</h2>

    <div class="mb-3! flex gap-2">
      <InputText id="task_header" autocomplete="off" v-model="new_task_status.name"
                 placeholder="New Task Status Name" class="flex-auto"/>
      <Button>Create</Button>
    </div>

    <details open>
      <summary><b>Select an icon for new task status</b></summary>

      <div class="icons_grid">
        <SelectButton v-model="new_task_status.icon" :options="icon_objects_normalized" dataKey="key" optionLabel="value">
          <template #option="slotProps">
            <unicon :name="slotProps.option.name" width="20" height="20" fill="#000" :icon-style="slotProps.option.style"></unicon>
          </template>
        </SelectButton>
      </div>
    </details>
  </div>
  <p>{{ new_task_status }}</p>
</settings-layout>
</template>

<style scoped></style>
