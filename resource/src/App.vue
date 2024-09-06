<template>
  <el-container class="layout-container-demo fluentgemini-container" style="height: 100vh">
    <el-aside :width="asideWidth" @mouseover="mouseOverFunction" @mouseleave="mouseOutFunction" class="fluentgemini-bg-gray-200">
      <el-header class="fluentgemini-flex fluentgemini-justify-between fluentgemini-items-center">
        <div>
          <el-icon style="margin-right: 8px; margin-top: 1px" class="fluentgemini-text-xl" >
            <icon-menu />
          </el-icon>
        </div>
        
        <div>
          <el-icon style="margin-top: 1px" class="fluentgemini-text-xl fluentgemini-cursor-pointer" v-if="showTitle" @click="toggleAsideBar">
            <setting />
          </el-icon>
        </div>
      </el-header>
      <el-scrollbar>
        <div v-for="item in menuItems" :key="item.route">
          <div class="fluentgemini-py-3 fluentgemini-px-5 fluentgemini-cursor-pointer hover:fluentgemini-bg-gray-100 fluentgemini-flex fluentgemini-items-center">
            <div>
              <el-icon style="margin-right: 8px;" class="fluentgemini-text-xl">
                <icon-menu />
              </el-icon>
            </div>
            <div class="fluentgemini-text-lg" v-if="showTitle">
              {{ item.title }}
            </div>
          </div>
        </div>
      </el-scrollbar>
    </el-aside>

    <el-container>
      <el-header style="text-align: right; font-size: 12px">
        <div class="toolbar">
          <el-dropdown>
            <el-icon style="margin-right: 8px; margin-top: 1px" class="fluentgemini-text-xl">
              <setting />
            </el-icon>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item>View</el-dropdown-item>
                <el-dropdown-item>Add</el-dropdown-item>
                <el-dropdown-item>Delete</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
          <span class="fluentgemini-text-xl">Tom</span>
        </div>
      </el-header>

      <el-main>
        <router-view></router-view>
      </el-main>
    </el-container>
  </el-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Menu as IconMenu, Message, Setting } from '@element-plus/icons-vue'

const menuItems = ref([
  { route: '/', title: 'Generate Text' },
  { route: '/', title: 'Process Document' },
  { route: '/', title: 'Generate Image' },
  { route: '/', title: 'Process Image' },
  { route: '/', title: 'Generate Code' },
])

const screenWidth = ref(window.innerWidth);

const asideWidth = ref('300px');
const showTitle = ref(true);
const isAsideVisible = ref(true)


if(screenWidth.value <= 768){
  asideWidth.value ='60px';
  showTitle.value = false;
  isAsideVisible.value = false
}



const mouseOverFunction = () => {
  if(!isAsideVisible.value) {
    asideWidth.value = '300px';
    showTitle.value = true;
  }
}

const mouseOutFunction = () => {
  if(!isAsideVisible.value) {
    asideWidth.value = '60px';
    showTitle.value = false;
  }
}

const toggleAsideBar = () => {
  isAsideVisible.value = !isAsideVisible.value
  if(isAsideVisible.value) {
    asideWidth.value = '300px';
    showTitle.value = true;
  }
  else {
    asideWidth.value = '60px';
    showTitle.value = false;
  }
}


</script>

<style scoped>
.layout-container-demo .el-header {
  position: relative;
  color: var(--el-text-color-primary);
}
.layout-container-demo .el-aside {
  color: var(--el-text-color-primary);
  transition: width 0.3s ease-in;
}
.layout-container-demo .el-menu {
  border-right: none;
}
.layout-container-demo .el-main {
  padding: 0px 20px;
}
.layout-container-demo .toolbar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  right: 20px;
}
</style>

<style scoped>
/* Add styles if needed */
</style>
