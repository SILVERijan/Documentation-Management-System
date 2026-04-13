import { ref, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

export type Theme = 'light' | 'dark'

// Global state singleton
const theme = ref<Theme>('dark')
let isInitialized = false

export function useTheme() {
    if (!isInitialized) {
        const page = usePage()
        const userTheme = page.props.auth?.user?.theme_preference
        
        if (userTheme && userTheme !== 'system') {
            theme.value = userTheme as Theme
        } else {
            theme.value = (localStorage.getItem('theme') as Theme) || 
                          (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
        }
        isInitialized = true
    }

    const applyTheme = (newTheme: Theme) => {
        const root = window.document.documentElement
        if (newTheme === 'dark') {
            root.classList.add('dark')
        } else {
            root.classList.remove('dark')
        }
        localStorage.setItem('theme', newTheme)
    }

    onMounted(() => {
        // Initial apply in case it wasn't caught by the blade script
        applyTheme(theme.value)
    })

    const setTheme = (val: Theme) => {
        theme.value = val
        applyTheme(val)
        
        const page = usePage()
        if (page.props.auth?.user) {
            // @ts-ignore - route is provided globally by Ziggy
            router.patch(route('profile.theme'), { theme: val }, {
                preserveScroll: true,
                preserveState: true,
            })
        }
    }

    return {
        theme,
        setTheme
    }
}
