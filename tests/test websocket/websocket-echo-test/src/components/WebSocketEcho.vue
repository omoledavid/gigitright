<template>
    <div>
      <h2>{{ status }}</h2>
      <ul>
        <li v-for="(msg, index) in messages" :key="index">{{ msg }}</li>
      </ul>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
  import echo from '../echo'
  
  const status = ref('Connecting...')
  const messages = ref([])
  
  onMounted(() => {
    status.value = 'Connected (Echo)'
  
    // Public channel
    echo.channel('testing')
      .listen('TestEvent', (data) => {
        messages.value.push(`Received: ${JSON.stringify(data)}`)
        console.log('Broadcast:', data)
      })
  
    // You can use axios if needed
    // axios.get('/api/some-endpoint').then(response => {
    //   console.log(response.data)
    // })
  })
  </script>
  