# How to build a new image

You have to login to docker with `theopenscholar` account.

```
(cd docker/travis)
docker build -t theopenscholar/openscholar-env .
docker push theopenscholar/openscholar-env
```