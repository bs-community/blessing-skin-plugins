<script lang="ts">
  import { onMount } from 'svelte'

  const { base_url, fetch, notify, t } = globalThis.blessing

  type Player = {
    pid: number
    name: string
    uid: number
    tid_skin: number
    tid_cape: number
    last_modified: string
  }

  let isLoading = false
  let isSubmitting = false

  let players: string[] = []
  let selected = ''

  onMount(async () => {
    isLoading = true
    try {
      const data = await fetch.get<Player[]>('/user/player/list')
      players = data.map(({ name }) => name)
      selected = players[0]
    } finally {
      isLoading = false
    }
  })

  async function handleSubmit() {
    isSubmitting = true
    try {
      const {
        code,
        message,
      }: {
        code: number
        message: string
      } = await fetch.post('/user/player/bind', {
        player: selected,
      })
      if (code === 0) {
        await notify.showModal({
          mode: 'alert',
          text: message,
        })
        location.assign(`${base_url}/user`)
      } else {
        notify.showModal({ mode: 'alert', text: message })
      }
    } finally {
      isSubmitting = false
    }
  }
</script>

{#if isLoading}
  <p>Loading...</p>
{:else}
  <form method="post" on:submit|preventDefault={handleSubmit}>
    {#if players.length > 0}
      <p>{t('single-player-limit.bindExistedPlayer')}</p>
      <div class="mb-3">
        {#each players as player (player)}
          <label class="d-block mb-1">
            <input
              type="radio"
              class="mr-2"
              checked={selected === player}
              on:change={() => (selected = player)}
            />
            {player}
          </label>
        {/each}
      </div>
    {:else}
      <p>{t('single-player-limit.bindNewPlayer')}</p>
      <input
        type="text"
        class="form-control mb-3"
        placeholder={t('general.player.player-name')}
        on:change={(e) => (selected = e.currentTarget.value)}
      />
    {/if}
    <button
      class="btn btn-primary float-right"
      type="submit"
      disabled={isSubmitting}
    >
      {#if isSubmitting}
        <i class="fas fa-spinner fa-spin mr-1" />
        {t('general.wait')}
      {:else}
        {t('general.submit')}
      {/if}
    </button>
  </form>
{/if}
