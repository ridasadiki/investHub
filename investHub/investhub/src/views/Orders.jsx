import {useEffect, useState} from "react";
import axiosClient from "../axios-client.js";
import {Link} from "react-router-dom";
import {useStateContext} from "../context/ContextProvider.jsx";

export default function Orders() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(false);
  const {setNotification} = useStateContext()

  useEffect(() => {
    getOrders();
  }, [])

  const onDeleteClick = order => {
    if (!window.confirm("Are you sure you want to delete this order?")) {
      return
    }
    axiosClient.delete(`/orders/${order.id}`)
      .then(() => {
        setNotification('Order was successfully deleted')
        getOrders()
      })
  }

  const getOrders = () => {
    setLoading(true)
    axiosClient.get('/orders')
      .then(({ data }) => {
        setLoading(false)
        setOrders(data.data)
      })
      .catch(() => {
        setLoading(false)
      })
  }

  return (
    <div>
      <div style={{display: 'flex', justifyContent: "space-between", alignItems: "center"}}>
        <h1>Orders</h1>
      </div>
      <div className="card animated fadeInDown">
        <table>
          <thead>
          <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Phone Number</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Address</th>
            <th>Status</th>
            <th>Order Date</th>
            <th>Actions</th>
          </tr>
          </thead>
          {loading &&
            <tbody>
            <tr>
              <td colSpan="5" className="text-center">
                Loading...
              </td>
            </tr>
            </tbody>
          }
          {!loading &&
            <tbody>
            {orders.map((u, index) => (
                <tr key={u.id}>
                <td>{index + 1}</td>
                <td>{u.first_name}</td>
                <td>{u.last_name}</td>
                <td>{u.phone_number}</td>
                <td>{u.name}</td>
                <td>{u.quantity}</td>
                <td>{u.location}</td>
                <td>{u.status}</td>
                <td>{u.created_at}</td>
                <td>
                    <Link className="btn-add" to={'/orders/' + u.id}>Update</Link>
                    &nbsp;
                    <button className="btn-delete" onClick={ev => onDeleteClick(u)}>Delete</button>
                </td>
                </tr>
            ))}
            </tbody>
          }
        </table>
      </div>
    </div>
  )
}
