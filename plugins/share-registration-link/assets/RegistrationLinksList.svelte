<script lang="ts">
  import { onMount } from 'svelte'

  type CodeRecord = {
    id: number
    sharer: number
    code: string
    url: string
  }

  let records: CodeRecord[] = []
  let sharer = 0
  let sharee = 0

  onMount(async () => {
    ;({ records, sharer, sharee } = await globalThis.blessing.fetch.get(
      '/user/reg-links',
    ))
  })

  async function handleDeleteClick({ id }: CodeRecord) {
    await globalThis.blessing.fetch.del(`/user/reg-links/${id}`)
    records = records.filter((record) => record.id !== id)
  }

  async function handleGenerateClick() {
    const {
      data: { record },
    }: { data: { record: CodeRecord } } = await globalThis.blessing.fetch.post(
      '/user/reg-links',
    )
    records = [...records, record]
  }
</script>

<div class="card card-primary card-outline">
  <div class="card-header">
    <h3 class="card-title">分享注册链接</h3>
  </div>
  <div class="card-body">
    <p>
      分享注册链接，当新用户使用此链接时，您将获得 {sharer} 积分。 同时新用户可获得
      {sharee} 积分。
    </p>
    {#if records.length > 0}
      <p>可用的链接：</p>
      <ul class="break-word">
        {#each records as record (record.id)}
          <li>
            <span class="mr-1">{record.url}</span>
            <button
              class="btn btn-danger"
              on:click={() => handleDeleteClick(record)}
            >
              删除
            </button>
          </li>
        {/each}
      </ul>
    {:else}
      还没有已生成的链接。
    {/if}
  </div>
  <div class="card-footer">
    <button class="btn btn-primary" on:click={handleGenerateClick}>
      生成新链接
    </button>
  </div>
</div>

<style>
  .break-word {
    word-wrap: break-word;
  }
</style>
