import {useNavigate, useParams} from "react-router-dom";
import {useEffect, useState} from "react";
import axiosClient from "../axios-client.js";
import {useStateContext} from "../context/ContextProvider.jsx";

export default function OrderForm() {
  const navigate = useNavigate();
  let {id} = useParams();
  const [order, setOrder] = useState({
    id: null,
    status: '',
  })
  const [errors, setErrors] = useState(null)
  const [loading, setLoading] = useState(false)
  const {setNotification} = useStateContext()

  if (id) {
    useEffect(() => {
      setLoading(true)
      axiosClient.get(`/orders/${id}`)
        .then(({data}) => {
          setLoading(false)
          setOrder(data)
        })
        .catch(() => {
          setLoading(false)
        })
    }, [])
  }

  const onSubmit = ev => {
    ev.preventDefault()
    if (order.id) {
      axiosClient.put(`/orders/${order.id}`, order)
        .then(() => {
          setNotification('Order was successfully updated')
          navigate('/orders')
        })
        .catch(err => {
          const response = err.response;
          if (response && response.status === 422) {
            setErrors(response.data.errors)
          }
        })
    } else {
      axiosClient.post('/orders', order)
        .then(() => {
          setNotification('Order was successfully created')
          navigate('/orders')
        })
        .catch(err => {
          const response = err.response;
          if (response && response.status === 422) {
            setErrors(response.data.errors)
          }
        })
    }
  }

  return (
    <>
      {order.id && <h1>Update Order: {order.name}</h1>}
      { <div>Customer Name: {order.first_name} {order.last_name}</div>}
      { <div>Phone Number: {order.phone_number}</div>}
      <div className="card animated fadeInDown">
        {loading && (
          <div className="text-center">
            Loading...
          </div>
        )}
        {errors &&
          <div className="alert">
            {Object.keys(errors).map(key => (
              <p key={key}>{errors[key][0]}</p>
            ))}
          </div>
        }
        {!loading && (
          <form onSubmit={onSubmit}>
              <select  value={order.status} onChange={(ev) => setOrder({ ...order, status: ev.target.value }) }>
                <option value="">--Select Status--</option>
                <option value="Pending">Pending</option>
                <option value="Transported">Transported</option>
                <option value="Delivered">Delivered</option>
              </select>
            <button className="btn">Save</button>
          </form>
        )}
      </div>
    </>
  )
}
