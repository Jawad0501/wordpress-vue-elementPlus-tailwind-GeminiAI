import { createApp } from 'vue'
import App from './App.vue'
import { ElButton, ElContainer, ElMain, ElHeader, ElAside, ElScrollbar, ElMenu, ElSubMenu, ElIcon, ElMenuItem, ElDropdown, ElDropdownItem, ElDropdownMenu } from 'element-plus'
import 'element-plus/es/components/button/style/css'
import 'element-plus/es/components/container/style/css'
import 'element-plus/es/components/header/style/css'
import 'element-plus/es/components/main/style/css'
import 'element-plus/es/components/aside/style/css'
import 'element-plus/es/components/scrollbar/style/css'
import 'element-plus/es/components/menu/style/css'
import 'element-plus/es/components/sub-menu/style/css'
import 'element-plus/es/components/icon/style/css'
import 'element-plus/es/components/menu-item/style/css'
import 'element-plus/es/components/dropdown/style/css'
import 'element-plus/es/components/dropdown-item/style/css'
import 'element-plus/es/components/dropdown-menu/style/css'
import {createRouter, createWebHashHistory} from 'vue-router';
import {routes} from './routes';
import './style.css';

const app = createApp(App)

const router = createRouter({
    routes,
    history: createWebHashHistory()
});

app.use(ElButton)
app.use(ElContainer)
app.use(ElMain)
app.use(ElHeader)
app.use(ElAside)
app.use(ElScrollbar)
app.use(ElIcon)
app.use(ElMenu)
app.use(ElSubMenu)
app.use(ElMenuItem)
app.use(ElDropdown)
app.use(ElDropdownItem)
app.use(ElDropdownMenu)
app.use(router)
app.mount('#fluentgemini_app')
