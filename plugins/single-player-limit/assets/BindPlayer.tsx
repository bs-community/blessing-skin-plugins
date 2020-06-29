import * as React from 'react'
import * as ReactDOM from 'react-dom'
import { fetch, notify, base_url, t } from 'blessing-skin'

type Player = {
  pid: number
  name: string
  uid: number
  tid_skin: number
  tid_cape: number
  last_modified: string
}

const BindPlayer: React.FC = () => {
  const [players, setPlayers] = React.useState<string[]>([])
  const [selected, setSelected] = React.useState('')
  const [isLoading, setIsLoading] = React.useState(false)
  const [isPending, setIsPending] = React.useState(false)

  React.useEffect(() => {
    const getPlayers = async () => {
      setIsLoading(true)
      const data = await fetch.get<Player[]>('/user/player/list')
      const players = data.map((player) => player.name)
      setPlayers(players)
      setSelected(players[0])
      setIsLoading(false)
    }
    getPlayers()
  }, [])

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setIsPending(true)

    const {
      code,
      message,
    }: {
      code: number
      message: string
    } = await fetch.post('/user/player/bind', { player: selected })
    if (code === 0) {
      await notify.showModal({ mode: 'alert', text: message })
      window.location.href = `${base_url}/user`
    } else {
      notify.showModal({ mode: 'alert', text: message })
    }

    setIsPending(false)
  }

  return isLoading ? (
    <p>Loading...</p>
  ) : (
    <form method="post" onSubmit={handleSubmit}>
      {players.length > 0 ? (
        <>
          <p>{t('single-player-limit.bindExistedPlayer')}</p>
          <div className="mb-3">
            {players.map((player) => (
              <label key={player} className="d-block mb-1">
                <input
                  type="radio"
                  className="mr-2"
                  checked={selected === player}
                  onChange={() => setSelected(player)}
                />
                {player}
              </label>
            ))}
          </div>
        </>
      ) : (
        <>
          <p>{t('single-player-limit.bindNewPlayer')}</p>
          <input
            type="text"
            className="form-control mb-3"
            placeholder={t('general.player.player-name')}
            onChange={(e) => setSelected(e.target.value)}
          />
        </>
      )}
      <button
        className="btn btn-primary float-right"
        type="submit"
        disabled={isPending}
      >
        {isPending ? (
          <>
            <i className="fas fa-spinner fa-spin mr-1"></i>
            {t('general.wait')}
          </>
        ) : (
          t('general.submit')
        )}
      </button>
    </form>
  )
}

ReactDOM.render(<BindPlayer />, document.querySelector('#form'))
