# CodeFair Test

## _A test API for CodeFair_

CodeFair-test is a repository, API-ready, that includes features like:

- User Authorization based on JWT
- Refresh tokens
- User registration
- Media

## Installation

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `make build` to build fresh images
3. Run `make up` (the logs will be displayed in the current shell)
4. Install the dependencies `make vendor`
5. Open `http://localhost:8001` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
6. Run `make down` to stop the Docker containers.

## Configuration

CodeFair Test is currently extended with the many plugins.
Each of them is configurable see **config/packages**.

## License

MIT

**Free Software, Hell Yeah!**
